<?php
namespace App\Controllers;
use App\Models\AiLogModel;
use CodeIgniter\API\ResponseTrait;

class Ai extends BaseController {
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
        
        if (!$prompt) return $this->fail('Prompt kosong', 400);
        $apiKey = getenv('GOOGLE_API_KEY');
        if (!$apiKey) return $this->fail('API Key Missing', 500);

        // STRATEGI: Coba 2.5-pro -> 1.5-pro -> 1.5-flash
        $models = ['gemini-2.5-pro', 'gemini-1.5-pro', 'gemini-1.5-flash'];
        $finalTxt = null; $errs = [];

        foreach ($models as $m) {
            $res = $this->callG($apiKey, $m, $prompt, $img);
            if ($res['ok']) { $finalTxt = $res['txt']; break; }
            $errs[] = "$m: " . $res['err'];
        }

        if (!$finalTxt) return $this->fail("Gagal: " . implode('|', $errs), 502);

        try { (new AiLogModel())->insert(['prompt'=>$prompt, 'response'=>$finalTxt]); } catch(\Exception $e){}
        return $this->respond(['result' => $finalTxt]);
    }

    private function callG($k, $m, $t, $i) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/$m:generateContent?key=$k";
        $pts = [['text'=>$t]];
        if($i && strlen($i)>100 && $m!=='gemini-pro') {
            if(strpos($i,',')!==false) $i=explode(',',$i)[1];
            $pts[]=['inline_data'=>['mime_type'=>'image/jpeg','data'=>$i]];
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_TIMEOUT=>60,
            CURLOPT_HTTPHEADER=>['Content-Type: application/json'],
            CURLOPT_POSTFIELDS=>json_encode(['contents'=>[['parts'=>$pts]]])]);
        $res=curl_exec($ch); $code=curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $d=json_decode($res,true);
        if($code===200 && isset($d['candidates'][0]['content']['parts'][0]['text']))
            return ['ok'=>true, 'txt'=>$d['candidates'][0]['content']['parts'][0]['text']];
        return ['ok'=>false, 'err'=>$d['error']['message']??$code];
    }
}
