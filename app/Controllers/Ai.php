<?php

namespace App\Controllers;

use App\Models\AiLogModel;
use CodeIgniter\API\ResponseTrait;

class Ai extends BaseController
{
    use ResponseTrait;

    public function index() { return view('nano_banana'); }

    public function history() {
        try {
            $model = new AiLogModel();
            $data = $model->orderBy('id', 'DESC')->findAll(20);
            return $this->respond(['history' => $data]);
        } catch (\Exception $e) { return $this->respond(['history' => []]); }
    }

    public function generate() {
        $this->response->setHeader('Content-Type', 'application/json');
        $json = $this->request->getJSON();
        $prompt = $json->prompt ?? '';
        $img = $json->image ?? null;
        
        // Menerima nama brand: "dev-pro" atau "dev-flash"
        $brandModel = $json->model ?? 'dev-pro';

        if (!$prompt) return $this->fail('Prompt kosong', 400);
        $apiKey = getenv('GOOGLE_API_KEY');
        if (!$apiKey) return $this->fail('API Key Error', 500);

        // --- MAPPING LOGIC ---
        // Menerjemahkan Brand -> Technical Model
        $modelMap = [
            'dev-pro'   => 'gemini-2.5-pro',
            'dev-flash' => 'gemini-2.5-flash'
        ];

        // Validasi & Fallback
        if (!array_key_exists($brandModel, $modelMap)) {
            $brandModel = 'dev-flash'; // Default safe
        }
        $targetModel = $modelMap[$brandModel];

        // --- SYSTEM INSTRUCTION ---
        $sysInst = ['parts' => [['text' => 
            "You are DEV daily ($brandModel), an elite engineering assistant. 
            Style: Concise, Technical, Modern. 
            Stack Preference: CI4, Tailwind, SQLite.
            Output: Markdown with clear code blocks."
        ]]];

        // --- CALL GOOGLE ---
        $res = $this->callGemini($apiKey, $targetModel, $prompt, $img, $sysInst);

        if (!$res['success']) {
            return $this->fail("Engine Failure ($brandModel): " . $res['error'], 502);
        }

        // --- LOGGING DENGAN NAMA BRAND ---
        try {
            $modelDB = new AiLogModel();
            $modelDB->insert([
                'prompt' => $prompt . " [$brandModel]", 
                'response' => $res['text']
            ]);
        } catch (\Exception $e) {}

        return $this->respond(['result' => $res['text']]);
    }

    private function callGemini($key, $model, $text, $image, $sys) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$key";
        $parts = [['text' => $text]];
        
        if ($image && strlen($image)>100) {
            if(strpos($image,',')!==false) $image=explode(',',$image)[1];
            $parts[]=['inline_data'=>['mime_type'=>'image/jpeg','data'=>$image]];
        }

        $body = [
            'contents' => [['role'=>'user', 'parts'=>$parts]],
            'system_instruction' => $sys
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_TIMEOUT=>60,
            CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
            CURLOPT_POSTFIELDS=>json_encode($body)
        ]);
        
        $res=curl_exec($ch); $code=curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $d=json_decode($res,true);
        
        if($code===200 && isset($d['candidates'][0]['content']['parts'][0]['text']))
            return ['success'=>true, 'text'=>$d['candidates'][0]['content']['parts'][0]['text']];
            
        return ['success'=>false, 'error'=>$d['error']['message']??$code];
    }
}
