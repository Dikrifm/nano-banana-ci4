<?php

namespace App\Services;

class GeminiService
{
    private $apiKey;
    private $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models/";

    public function __construct()
    {
        $this->apiKey = getenv('GOOGLE_API_KEY');
    }

    public function generateContent($model, $prompt, $image = null, $systemInstruction = [])
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'API Key missing'];
        }

        $url = $this->baseUrl . "$model:generateContent?key=" . $this->apiKey;
        
        // Siapkan Payload
        $parts = [['text' => $prompt]];
        if ($image && strlen($image) > 100) {
            if (strpos($image, ',') !== false) $image = explode(',', $image)[1];
            $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $image]];
        }

        $body = [
            'contents' => [['role' => 'user', 'parts' => $parts]],
            'system_instruction' => $systemInstruction
        ];

        // Eksekusi CURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($body)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => true, 'text' => $data['candidates'][0]['content']['parts'][0]['text']];
        }

        return ['success' => false, 'error' => $data['error']['message'] ?? "HTTP Error: $httpCode"];
    }
}

