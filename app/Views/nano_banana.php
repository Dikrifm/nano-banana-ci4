<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, interactive-widget=resizes-content">
<title>DEV.daily</title>

<!-- ASSETS LOKAL -->
<link rel="stylesheet" href="<?= base_url('assets/css/typography.css') ?>">
<script src="<?= base_url('assets/js/tailwind.js') ?>"></script>
<script src="<?= base_url('assets/js/lucide.js') ?>"></script>
<script src="<?= base_url('assets/js/marked.js') ?>"></script>
<script src="<?= base_url('assets/js/highlight.js') ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/highlight.css') ?>">

<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    navy: { DEFAULT: '#00091a', light: '#021b42', lighter: '#0b2550', text: '#011432' },
                    solar: { start: '#ffde59', end: '#ff914d' }
                },
                fontFamily: {
                    heading: ['Codex', 'serif'],
                    sub: ['Condensed', 'sans-serif'],
                    body: ['Roboto', 'sans-serif'],
                },
                backgroundImage: {
                    'solar-gradient': 'linear-gradient(135deg, #ffde59 0%, #ff914d 100%)',
                }
            }
        }
    }
</script>

<style>
    body { background-color: #00091a; color: #e2e8f0; font-family: 'Roboto', sans-serif; }
    ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
    
    /* Markdown */
    .prose { font-size: 0.95rem; line-height: 1.6; max-width: none; color: #cbd5e1; }
    .prose strong { color: #ffde59; font-weight: 700; }
    .prose h1, .prose h2, .prose h3 { color: #fff; font-family: 'Condensed', sans-serif; margin-top: 1em; font-weight: 700; }
    .prose code { color: #ff914d; font-family: monospace; background: rgba(255,255,255,0.05); padding: 2px 5px; border-radius: 4px; font-size: 0.9em; }
    .prose pre { background: #00050f !important; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; margin: 1em 0; overflow-x: auto; }
    .prose ul { list-style: disc; padding-left: 1.2em; }

    /* Code Header */
    .code-header { display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05); padding: 6px 12px; font-family: 'Condensed', sans-serif; font-size: 10px; color: #ffde59; text-transform: uppercase; letter-spacing: 1px; border-top-left-radius: 8px; border-top-right-radius: 8px; }
    .copy-btn { cursor: pointer; display: flex; align-items: center; gap: 4px; opacity: 0.7; transition: opacity 0.2s; }
    .copy-btn:hover { opacity: 1; }

    /* Custom Dropdown Animation */
    .dropdown-enter { animation: dropIn 0.2s ease-out forwards; transform-origin: bottom left; }
    @keyframes dropIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    
    .msg-enter { animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(20px); }
    @keyframes slideIn { to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body class="h-[100dvh] flex flex-col overflow-hidden relative">

    <!-- HEADER: CLEAN & CENTERED -->
    <header class="h-16 shrink-0 fixed top-0 w-full z-50 flex items-center justify-between px-5 bg-navy/80 backdrop-blur-md border-b border-white/5">
        <button onclick="toggleSidebar()" class="p-2 text-gray-400 hover:text-white"><i data-lucide="menu" class="w-5 h-5"></i></button>
        
        <div class="absolute left-1/2 transform -translate-x-1/2 select-none pointer-events-none">
            <span class="font-heading text-xl text-white tracking-tight leading-none">DEV<span class="text-transparent bg-clip-text bg-solar-gradient">.daily</span></span>
        </div>

        <button onclick="clearHistory()" class="p-2 text-gray-400 hover:text-red transition-colors">
            <i data-lucide="trash-2" class="w-5 h-5"></i>
        </button>
    </header>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-[#00050f] border-r border-white/5 transform -translate-x-full transition-transform duration-300 z-[60] flex flex-col pt-16">
        <div class="p-4 border-b border-white/5">
            <span class="text-[10px] font-sub font-bold text-gray-500 uppercase tracking-widest">System Info</span>
            <div class="mt-2 text-xs text-gray-300 font-heading">DEV OS 1.5</div>
        </div>
        <div class="flex-1 overflow-y-auto p-2" id="history-list"></div>
    </aside>
    <div id="overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[55] hidden transition-opacity"></div>

    <!-- MAIN AREA -->
    <main class="flex-1 overflow-y-auto scroll-smooth pt-16 pb-32" id="scroll-container">
        
        <!-- Welcome State -->
        <div id="welcome" class="hidden h-full flex flex-col items-center justify-center opacity-50 select-none pt-10">
            <img src="<?= base_url('assets/img/logo.png') ?>" class="h-16 object-contain mb-4 opacity-80 animate-pulse">
            <h2 class="font-heading text-lg text-gray-500">READY FOR INPUT</h2>
        </div>

        <!-- Chat Stream -->
        <div id="chat-stream" class="flex flex-col gap-6 p-4 md:p-6 max-w-4xl mx-auto"></div>

    </main>

    <!-- INPUT DECK: SMART CAPSULE -->
    <div class="fixed bottom-0 w-full p-4 z-50 bg-gradient-to-t from-[#00091a] via-[#00091a]/95 to-transparent pointer-events-none">
        <div class="max-w-4xl mx-auto pointer-events-auto relative">
            
            <!-- Image Preview (Popup) -->
            <div id="preview-area" class="hidden absolute -top-16 left-0 bg-navy-lighter border border-white/10 p-2 rounded-xl flex items-center gap-3 shadow-xl animate-fade-in">
                <img id="preview-img" class="h-10 w-10 rounded-lg object-cover border border-white/10">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-white font-sub">IMAGE ATTACHED</span>
                    <span class="text-[9px] text-gray-500">Ready to analyze</span>
                </div>
                <button onclick="clearImage()" class="p-1 hover:bg-white/10 rounded text-red"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <!-- CAPSULE CONTAINER -->
            <div class="bg-[#021b42]/80 backdrop-blur-xl border border-white/10 rounded-3xl p-1.5 flex flex-col shadow-2xl transition-all focus-within:border-white/30 focus-within:bg-[#021b42]">
                
                <!-- LEVEL 1: Textarea -->
                <textarea id="prompt" rows="1" 
                    class="w-full bg-transparent border-none text-gray-100 placeholder-gray-500 text-base focus:ring-0 resize-none px-4 pt-3 pb-1 font-body max-h-32 leading-relaxed"
                    placeholder="Input command..." oninput="autoGrow(this)" onkeydown="checkSubmit(event)"></textarea>

                <!-- LEVEL 2: Controls -->
                <div class="flex justify-between items-center px-2 pb-1 relative">
                    
                    <div class="flex items-center gap-2">
                        <!-- Upload Button -->
                        <button onclick="document.getElementById('f').click()" class="p-2 rounded-full hover:bg-white/5 text-gray-400 hover:text-white transition-colors">
                            <i data-lucide="paperclip" class="w-5 h-5"></i>
                            <input type="file" id="f" class="hidden" accept="image/*" onchange="handleFile(this)">
                        </button>

                        <!-- CUSTOM MODEL SELECTOR (Pill) -->
                        <div class="relative">
                            <button onclick="toggleModelMenu()" id="model-trigger" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/5 hover:border-white/20 transition-all group">
                                <span id="current-model-label" class="text-[10px] font-heading font-bold text-gray-300 uppercase tracking-wide group-hover:text-white">DEV Pro 1.0</span>
                                <i data-lucide="chevron-up" class="w-3 h-3 text-gray-500 group-hover:text-white transition-transform" id="model-arrow"></i>
                            </button>

                            <!-- POPUP MENU (Hidden by default) -->
                            <div id="model-menu" class="hidden absolute bottom-full left-0 mb-2 w-48 bg-[#0b1829] border border-white/10 rounded-xl shadow-2xl p-1 z-50 dropdown-enter overflow-hidden">
                                <div onclick="selectModel('dev-pro', 'DEV PRO 1.0')" class="p-2.5 rounded-lg hover:bg-white/5 cursor-pointer flex items-center gap-3 group">
                                    <div class="w-8 h-8 rounded-lg bg-solar-gradient flex items-center justify-center shadow-lg">
                                        <i data-lucide="activity" class="w-4 h-4 text-navy"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-white font-heading">DEV Pro 1.0</span>
                                        <span class="text-[9px] text-gray-500">Deep Logic & Creative</span>
                                    </div>
                                </div>
                                <div onclick="selectModel('dev-flash', 'DEV FLASH 1.0')" class="p-2.5 rounded-lg hover:bg-white/5 cursor-pointer flex items-center gap-3 group mt-1">
                                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center border border-white/5">
                                        <i data-lucide="zap" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-400 group-hover:text-white font-heading transition-colors">DEV Flash 1.0</span>
                                        <span class="text-[9px] text-gray-600 group-hover:text-gray-500">High Speed & Concise</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden Input to store value -->
                            <input type="hidden" id="selected-model-value" value="dev-pro">
                        </div>
                    </div>

                    <!-- Send Button -->
                    <button onclick="run()" id="btn-send" class="p-2.5 rounded-full bg-solar-gradient text-navy-text shadow-lg hover:scale-105 active:scale-95 transition-transform disabled:opacity-50 disabled:grayscale">
                        <i data-lucide="arrow-up" class="w-5 h-5 font-bold"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let b64 = null;

        // --- MODEL SELECTOR LOGIC ---
        function toggleModelMenu() {
            const menu = document.getElementById('model-menu');
            const arrow = document.getElementById('model-arrow');
            menu.classList.toggle('hidden');
            arrow.style.transform = menu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function selectModel(value, label) {
            document.getElementById('selected-model-value').value = value;
            document.getElementById('current-model-label').innerText = label;
            toggleModelMenu();
            // Visual Feedback
            const trigger = document.getElementById('model-trigger');
            trigger.classList.add('ring-1', 'ring-solar-start');
            setTimeout(() => trigger.classList.remove('ring-1', 'ring-solar-start'), 300);
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('model-menu');
            const trigger = document.getElementById('model-trigger');
            if (!trigger.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
                document.getElementById('model-arrow').style.transform = 'rotate(0deg)';
            }
        });

        // --- RENDER ENGINE ---
        function createBlock(role, text, time, isNew = false) {
            const container = document.createElement('div');
            container.className = `flex flex-col ${isNew ? 'msg-enter' : ''} ${role === 'user' ? 'items-end' : 'items-start'}`;
            
            // Header Logic
            let headerHTML = '';
            if (role === 'user') {
                headerHTML = `
                    <div class="flex items-center gap-2 mb-1 px-2 flex-row-reverse opacity-60 select-none">
                        <span class="font-sub font-bold text-[10px] text-gray-400 tracking-wider">YOU</span>
                    </div>`;
            } else {
                headerHTML = `
                    <div class="flex items-center gap-2 mb-2 px-0 select-none">
                        <span class="font-heading text-sm text-white tracking-tight leading-none">DEV<span class="text-transparent bg-clip-text bg-solar-gradient">.daily</span></span>
                    </div>`;
            }

            let contentHTML = '';
            if (role === 'user') {
                contentHTML = `
                    <div class="max-w-[85%] md:max-w-2xl bg-solar-gradient rounded-2xl rounded-tr-sm px-5 py-3 shadow-md">
                        <div class="prose max-w-none text-navy-text font-body leading-relaxed font-medium text-sm md:text-base">${text}</div>
                    </div>
                    <span class="text-[9px] text-gray-600 mt-1 mr-1 font-sub block text-right">${time}</span>
                `;
            } else {
                // AI Block with Footer
                contentHTML = `
                    <div class="w-full md:max-w-3xl pl-0">
                        <div class="prose prose-invert max-w-none text-gray-300 font-body leading-relaxed mb-3 text-sm md:text-base">
                            ${marked.parse(text)}
                        </div>
                        <!-- ACTION FOOTER -->
                        <div class="flex items-center gap-4 pt-2 border-t border-white/5 opacity-80 hover:opacity-100 transition-opacity">
                            <button onclick="copyResponse(this)" class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-white/5 transition-colors group">
                                <i data-lucide="copy" class="w-3 h-3 text-gray-500 group-hover:text-solar-start"></i>
                                <span class="text-[10px] font-sub text-gray-500 group-hover:text-white uppercase tracking-widest">Salin</span>
                            </button>
                            <button onclick="window.location.reload()" class="flex items-center gap-1.5 px-2 py-1 rounded hover:bg-white/5 transition-colors group">
                                <i data-lucide="rotate-cw" class="w-3 h-3 text-gray-500 group-hover:text-solar-start"></i>
                            </button>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = headerHTML + contentHTML;

            if (role === 'ai') {
                container.querySelectorAll('pre code').forEach(block => {
                    hljs.highlightElement(block);
                    addCodeHeader(block);
                });
            }
            return container;
        }

        // --- UTILS & LOGIC ---
        function addCodeHeader(block) {
            const pre = block.parentElement; pre.classList.remove('hljs');
            const lang = block.className.replace('hljs language-', '').toUpperCase() || 'CODE';
            const header = document.createElement('div'); header.className = 'code-header';
            header.innerHTML = `<span>${lang}</span><div class="copy-btn" onclick="copyCode(this)"><i data-lucide="copy" width="12"></i></div>`;
            pre.insertBefore(header, block); lucide.createIcons();
        }
        window.copyCode = function(btn) {
            const code = btn.parentElement.nextElementSibling.innerText; navigator.clipboard.writeText(code);
            const o = btn.innerHTML; btn.innerHTML = '<i data-lucide="check" width="12" class="text-green-500"></i>';
            setTimeout(()=>btn.innerHTML=o,2000); lucide.createIcons();
        }
        window.copyResponse = function(btn) {
            // Find text content (prose div is previous sibling of footer's parent)
            const footer = btn.parentElement;
            const prose = footer.previousElementSibling;
            navigator.clipboard.writeText(prose.innerText);
            
            const span = btn.querySelector('span');
            const ori = span.innerText;
            span.innerText = "DISALIN";
            span.classList.add('text-green-500');
            setTimeout(()=>{ 
                span.innerText = ori; 
                span.classList.remove('text-green-500'); 
            }, 2000);
        }

        async function run() {
            const pIn = document.getElementById('prompt'); const p = pIn.value.trim(); 
            const m = document.getElementById('selected-model-value').value; // Get value from hidden input
            if(!p) return;
            const btn = document.getElementById('btn-send'); const stream = document.getElementById('chat-stream');
            const now = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
            
            btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" class="animate-spin w-5 h-5"></i>';
            document.getElementById('welcome').classList.add('hidden');
            lucide.createIcons();

            stream.appendChild(createBlock('user', p, now, true)); scrollToBottom();
            pIn.value=''; pIn.style.height='auto'; clearImage();

            try {
                const res = await fetch('/api/generate', {
                    method: 'POST', headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({prompt:p, image:b64, model:m})
                });
                const d = await res.json();
                if(d.result) { stream.appendChild(createBlock('ai', d.result, now, true)); } 
                else { throw new Error(d.messages?.error||"Error"); }
            } catch(e) { stream.appendChild(createBlock('ai', `**ERR:** ${e.message}`, now, true)); } 
            finally { btn.disabled=false; btn.innerHTML='<i data-lucide="arrow-up" class="w-6 h-6"></i>'; lucide.createIcons(); scrollToBottom(); }
        }
        
        async function loadHistory() {
            try {
                const res = await fetch('/api/history'); const d = await res.json();
                if(!d.history || d.history.length===0) { document.getElementById('welcome').classList.remove('hidden'); return; }
                d.history.reverse().forEach(i => {
                    const t = new Date(i.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                    const stream = document.getElementById('chat-stream');
                    stream.appendChild(createBlock('user', i.prompt, t));
                    stream.appendChild(createBlock('ai', i.response, t));
                });
                lucide.createIcons(); scrollToBottom();
            } catch(e){}
        }

        function scrollToBottom() { setTimeout(() => { document.getElementById('scroll-container').scrollTop = document.getElementById('scroll-container').scrollHeight; }, 100); }
        function autoGrow(el) { el.style.height='auto'; el.style.height=el.scrollHeight+'px'; }
        function checkSubmit(e) { if(e.key==='Enter' && !e.shiftKey) { e.preventDefault(); run(); } }
        function handleFile(i){ if(i.files[0]) { const r=new FileReader(); r.onload=e=>{ b64=e.target.result; document.getElementById('preview-img').src=b64; document.getElementById('preview-area').classList.remove('hidden'); }; r.readAsDataURL(i.files[0]); }}
        function clearImage() { b64=null; document.getElementById('f').value=''; document.getElementById('preview-area').classList.add('hidden'); }
        function toggleSidebar() { 
            const sb=document.getElementById('sidebar'); const ov=document.getElementById('overlay');
            if(sb.classList.contains('-translate-x-full')) { sb.classList.remove('-translate-x-full'); ov.classList.remove('hidden'); }
            else { sb.classList.add('-translate-x-full'); ov.classList.add('hidden'); }
        }
        function clearHistory() { document.getElementById('chat-stream').innerHTML=''; document.getElementById('welcome').classList.remove('hidden'); }
        window.onload = loadHistory;
    </script>
</body>
</html>
