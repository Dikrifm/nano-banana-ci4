<?php

namespace App\Controllers;

use App\Models\AiLogModel;
use App\Services\GeminiService; // Panggil Service baru
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
    
    // --- FITUR BARU: DELETE HISTORY ---
    public function clear() {
        try {
            $model = new AiLogModel();
            $model->truncate(); // Menghapus SEMUA isi tabel ai_logs
            return $this->respond(['status' => 'success', 'message' => 'History wiped.']);
        } catch (\Exception $e) {
            return $this->fail('Gagal menghapus database.', 500);
        }
    }

    public function generate() {
        $this->response->setHeader('Content-Type', 'application/json');
        $json = $this->request->getJSON();
        
        $prompt = $json->prompt ?? '';
        $img = $json->image ?? null;
        $brandModel = $json->model ?? 'dev-pro';

        if (!$prompt) return $this->fail('Prompt kosong', 400);

        // Mapping Model
        $modelMap = [
            'dev-pro'   => 'gemini-2.5-pro', // Update ke model terbaru jika mau
            'dev-flash' => 'gemini-2.5-flash'
        ];
        $targetModel = $modelMap[$brandModel] ?? 'gemini-1.5-flash';

        // System Instruction
        $sysInst = ['parts' => [['text' =>
            "You are DEV daily ($brandModel), an elite engineering assistant.
            Style: Concise, Technical, Modern.
            Stack Preference: CI4, Tailwind, MySQL. 
            Output: Markdown with clear code blocks."
        ]]];

        // --- PANGGIL SERVICE (Logic dipisah) ---
        $gemini = new GeminiService();
        $res = $gemini->generateContent($targetModel, $prompt, $img, $sysInst);

        if (!$res['success']) {
            return $this->fail("Engine Failure ($brandModel): " . $res['error'], 502);
        }

        // Log ke Database
        try {
            $modelDB = new AiLogModel();
            $modelDB->insert([
                'prompt' => $prompt . " [$brandModel]",
                'response' => $res['text']
            ]);
        } catch (\Exception $e) {}

        return $this->respond(['result' => $res['text']]);
    }
}
