<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Nano Banana 2.5</title>
<script src="https://cdn.tailwindcss.com"></script><script src="https://unpkg.com/lucide@latest"></script>
<style>body{background:#000;color:#eee;font-family:monospace}.no-scroll::-webkit-scrollbar{display:none}</style>
</head>
<body class="h-[100dvh] w-full flex flex-col bg-black overflow-hidden">

<header class="h-14 shrink-0 border-b border-gray-800 bg-[#0a0a0a] flex items-center justify-between px-4">
    <div class="flex items-center gap-2">
        <div class="bg-yellow-500 text-black p-1 rounded"><i data-lucide="banana" class="w-5 h-5"></i></div>
        <div class="leading-none">
            <h1 class="font-bold text-lg">NANO BANANA</h1>
            <p class="text-[9px] text-green-500 font-bold">SERIES 2.5</p>
        </div>
    </div>
    <!-- MODEL SELECTOR TERBARU -->
    <select id="model-select" class="bg-[#111] border border-gray-700 text-gray-300 text-xs rounded px-2 py-1 outline-none focus:border-yellow-500 font-bold uppercase">
        <option value="gemini-2.5-pro">🍌 2.5 PRO (MAX)</option>
        <option value="gemini-2.5-flash">⚡ 2.5 FLASH (FAST)</option>
    </select>
</header>

<main class="flex-1 flex flex-col relative w-full bg-black min-h-0">
    <div id="output" class="flex-1 overflow-y-auto p-4 space-y-4 pb-4">
        <div id="prev-box" class="hidden flex justify-center"><img id="prev-img" class="h-32 rounded border border-gray-700 object-contain"></div>
        <div id="result" class="hidden p-3 bg-gray-900 border border-gray-800 rounded text-sm whitespace-pre-wrap leading-relaxed text-gray-300"></div>
        <div id="idle" class="h-full flex flex-col items-center justify-center opacity-20">
            <i data-lucide="cpu" class="w-12 h-12 mb-2"></i><p class="text-xs">READY FOR 2.5</p>
        </div>
    </div>

    <div class="shrink-0 p-3 bg-[#0a0a0a] border-t border-gray-800 w-full z-20">
        <div class="flex gap-2 mb-2">
            <div onclick="document.getElementById('f').click()" class="p-3 bg-[#111] border border-gray-800 rounded cursor-pointer active:bg-gray-700">
                <i data-lucide="image" class="w-5 h-5 text-gray-500" id="ic-img"></i>
                <input type="file" id="f" class="hidden" accept="image/*" onchange="loadFile(this)">
            </div>
            <textarea id="p" class="flex-1 bg-black border border-gray-800 rounded p-3 text-sm focus:border-yellow-500 outline-none h-[48px] resize-none" placeholder="Perintah..."></textarea>
        </div>
        <button onclick="run()" id="btn" class="w-full bg-yellow-500 text-black font-bold py-3 rounded flex justify-center items-center gap-2 active:scale-95 transition-transform">
            <i data-lucide="zap" class="w-5 h-5 fill-current"></i> GENERATE
        </button>
    </div>
</main>

<script>
    lucide.createIcons(); let b64 = null;
    function loadFile(i){if(i.files[0]){let r=new FileReader();r.onload=e=>{b64=e.target.result;document.getElementById("prev-img").src=b64;document.getElementById("prev-box").classList.remove("hidden");document.getElementById("ic-img").classList.add("text-green-500")};r.readAsDataURL(i.files[0])}}
    
    async function run() {
        const p = document.getElementById("p").value; 
        const m = document.getElementById("model-select").value; 
        if(!p) return alert("Isi prompt!");
        
        const btn = document.getElementById("btn");
        btn.disabled = true; 
        btn.innerHTML = `<span class="animate-pulse">REQ: ${m}...</span>`;
        
        document.getElementById("idle").classList.add("hidden");
        document.getElementById("result").innerText = "";
        
        try {
            const res = await fetch("/api/generate", {
                method: "POST", 
                headers: {"Content-Type":"application/json", "X-Requested-With":"XMLHttpRequest"},
                body: JSON.stringify({ prompt: p, image: b64, model: m })
            });
            const d = await res.json();
            if(d.result) {
                document.getElementById("result").innerText = d.result;
                document.getElementById("result").classList.remove("hidden");
            } else {
                alert(d.messages?.error || JSON.stringify(d));
            }
        } catch(e) { alert("SysErr: "+e.message); } 
        finally {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="zap" class="w-5 h-5 fill-current"></i> GENERATE`;
            lucide.createIcons();
        }
    }
</script></body></html>
