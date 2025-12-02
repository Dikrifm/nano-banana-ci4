<?php

namespace App\Controllers;

use App\Models\AiLogModel;
use CodeIgniter\API\ResponseTrait;

class Ai extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        return view('nano_banana');
    }

    public function history()
    {
        try {
            $model = new AiLogModel();
            $data = $model->orderBy('id', 'DESC')->findAll(20);
            return $this->respond(['history' => $data]);
        } catch (\Exception $e) { return $this->respond(['history' => []]); }
    }

    public function generate()
    {
        // 1. Force JSON
        $this->response->setHeader('Content-Type', 'application/json');

        $json = $this->request->getJSON();
        $prompt = $json->prompt ?? '';
        $imageBase64 = $json->image ?? null;
        // Default ke 2.5-flash jika user tidak memilih
        $selectedModel = $json->model ?? 'gemini-2.5-flash';

        if (!$prompt) return $this->fail('Prompt kosong.', 400);

        $apiKey = getenv('GOOGLE_API_KEY');
        if (!$apiKey) return $this->fail('API Key Error.', 500);

        // 2. WHITELIST MODEL (Hanya 2.5 Series)
        $validModels = [
            'gemini-2.5-pro', 
            'gemini-2.5-flash'
        ];

        // Validasi Keras
        if (!in_array($selectedModel, $validModels)) {
            $selectedModel = 'gemini-2.5-flash';
        }

        // 3. EKSEKUSI
        $res = $this->callGemini($apiKey, $selectedModel, $prompt, $imageBase64);

        if (!$res['success']) {
            return $this->fail("Gagal ($selectedModel): " . $res['error'], 502);
        }

        // 4. LOGGING
        try {
            $modelDB = new AiLogModel();
            $modelDB->insert([
                'prompt' => $prompt . " [$selectedModel]", 
                'response' => $res['text']
            ]);
        } catch (\Exception $e) {}

        return $this->respond(['result' => $res['text']]);
    }

    private function callGemini($key, $model, $text, $image) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$key";
        
        $parts = [['text' => $text]];
        
        if ($image && strlen($image) > 100) {
            if (strpos($image, ',') !== false) $image = explode(',', $image)[1];
            $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $image]];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode(['contents' => [['parts' => $parts]]]),
            CURLOPT_TIMEOUT => 60
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) return ['success' => false, 'error' => "Koneksi: $curlErr"];

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => true, 'text' => $data['candidates'][0]['content']['parts'][0]['text']];
        }

        $msg = $data['error']['message'] ?? "HTTP $httpCode";
        return ['success' => false, 'error' => $msg];
    }
}
