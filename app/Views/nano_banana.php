<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"><title>Nano Banana</title><script src="https://cdn.tailwindcss.com"></script><script src="https://unpkg.com/lucide@latest"></script><style>body{background:#000;color:#eee;font-family:monospace}::-webkit-scrollbar{display:none}</style></head>
<body class="h-[100dvh] flex flex-col bg-black overflow-hidden">
<header class="h-14 shrink-0 border-b border-gray-800 flex items-center justify-between px-4 bg-[#0a0a0a]">
<div class="flex gap-2 items-center"><span class="bg-yellow-500 text-black p-1 rounded"><i data-lucide="banana" class="w-5 h-5"></i></span><span class="font-bold">NANO BANANA</span></div><span class="text-[10px] text-green-500 border border-green-900 px-2 rounded">V2.0</span></header>
<main class="flex-1 flex flex-col min-h-0 relative"><div id="out" class="flex-1 overflow-y-auto p-4 space-y-4"><div id="prev" class="hidden flex justify-center"><img id="img" class="h-32 rounded border border-gray-700"></div><div id="res" class="hidden p-3 bg-gray-900 border border-gray-800 rounded text-sm whitespace-pre-wrap"></div><div id="idle" class="h-full flex items-center justify-center opacity-20 text-xs">READY</div></div>
<div class="shrink-0 p-3 border-t border-gray-800 bg-[#0a0a0a] z-20"><div class="flex gap-2 mb-2"><div onclick="document.getElementById('f').click()" class="p-3 bg-[#111] border border-gray-800 rounded"><i data-lucide="image" class="w-5 h-5 text-gray-500" id="ic"></i><input type="file" id="f" class="hidden" onchange="loadFile(this)"></div><textarea id="p" class="flex-1 bg-black border border-gray-800 rounded p-3 text-sm outline-none h-[48px]" placeholder="..."></textarea></div><button onclick="run()" id="btn" class="w-full bg-yellow-500 text-black font-bold py-3 rounded flex justify-center gap-2"><i data-lucide="zap" class="w-5 h-5"></i> GENERATE</button></div></main>
<script>lucide.createIcons();let b64=null;
function loadFile(i){if(i.files[0]){let r=new FileReader();r.onload=e=>{b64=e.target.result;document.getElementById("img").src=b64;document.getElementById("prev").classList.remove("hidden");document.getElementById("ic").classList.add("text-green-500")};r.readAsDataURL(i.files[0])}}
async function run(){let p=document.getElementById("p").value;if(!p)return alert("Isi prompt!");
let btn=document.getElementById("btn");btn.disabled=true;btn.innerText="PROCESSING...";
document.getElementById("idle").classList.add("hidden");document.getElementById("res").innerText="";
try{let r=await fetch("/api/generate",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({prompt:p,image:b64})});
let d=await r.json();
if(d.result){document.getElementById("res").innerText=d.result;document.getElementById("res").classList.remove("hidden");}
else{alert(d.messages?.error||JSON.stringify(d));}
}catch(e){alert("Err: "+e.message);}finally{btn.disabled=false;btn.innerHTML="<i data-lucide='zap' class='w-5 h-5'></i> GENERATE";lucide.createIcons();}}
</script></body></html>
