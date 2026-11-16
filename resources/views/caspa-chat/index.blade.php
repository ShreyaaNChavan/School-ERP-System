@extends('layouts.schoolnexa')
@section('content')
    @include('caspa-chat._style')
    <div class="ci-page">
        <div class="ci-head">
            <div>
                <h1 class="ci-title">CASPA Chat <span class="ci-title-badge">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13z"/></svg>
                        AI</span></h1>
                <p class="ci-sub">SUBCASPA Chat</p>
            </div>
            <div class="ci-actions"><a class="ci-btn" href="{{ route('dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    Back to Dashboard</a></div>
        </div>

        <div class="ci-layout">
            <section class="ci-main">
                <div class="ci-hero">
                    <div class="ci-hero-ico">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13z"/></svg>
                    </div>
                    <h2>Ask CASPA anything about your work, learning or SchoolNexa.</h2>
                    <p>Powered by your existing Gemini integration. Responses are generated using the signed-in account's
                        authorized context.</p>
                </div>

                <div class="ci-stat">
                    <span class="ci-chip"><i></i>Signed in: {{ auth()->user()->name }}</span>
                    <span class="ci-chip">Role: {{ ucfirst($profile['role']) }}</span>
                    <span class="ci-chip">School scope: {{ $profile['school_id'] }}</span>
                </div>

                <div class="ci-prompts" id="caspaPrompts">
                    <button type="button" class="ci-prompt"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2z"/></svg><span>Explain how attendance is calculated</span></button>
                    <button type="button" class="ci-prompt"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2z"/></svg><span>Help me understand my academic progress</span></button>
                    <button type="button" class="ci-prompt"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2z"/></svg><span>Summarize my responsibilities</span></button>
                </div>

                {{-- Live conversation thread --}}
                <div class="ci-thread" id="caspaThread" aria-live="polite"></div>

                <form class="ci-compose" id="caspaForm" novalidate>
                    @csrf
                    <div class="ci-compose-box">
                        <textarea id="caspaMessage" name="message" maxlength="8000" required
                            placeholder="Type your question here..."></textarea>
                        <div class="ci-compose-foot">
                            <div class="ci-foot-left">
                                <span class="ci-count" id="caspaCount">0 / 8000</span>
                                <span class="ci-kbd"><kbd>Enter</kbd> to send &middot; <kbd>Shift</kbd>+<kbd>Enter</kbd> for new line</span>
                            </div>
                            <button class="ci-btn ai" type="submit" id="caspaSend" disabled>
                                <span class="ci-spin"></span>
                                <svg class="ci-send-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13z"/></svg>
                                <span id="caspaSendLabel">Send to CASPA</span>
                            </button>
                        </div>
                    </div>
                    <div class="ci-muted ci-note">Do not enter passwords, API keys or highly sensitive personal information.</div>
                    <div class="ci-error" id="caspaError" role="alert">
                        <span id="caspaErrorText"></span>
                        <button type="button" class="ci-btn sm" id="caspaRetry">Retry</button>
                    </div>
                </form>
            </section>

            <aside class="ci-side">
                <h3>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#5B3FA3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    My Recent Conversations
                </h3>
                <div class="ci-history">
                    @forelse($history as $item)
                        <a href="{{ route('caspa-chat.conversation', $item->id) }}">{{ $item->title }}
                            <div class="ci-muted">{{ \Illuminate\Support\Carbon::parse($item->updated_at)->diffForHumans() }}</div>
                        </a>
                    @empty
                        <div class="ci-history-empty">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2z"/></svg>
                            <p class="ci-muted">Your conversations will appear here.</p>
                        </div>
                    @endforelse
                </div>
                <p class="ci-muted" style="margin-top:14px">Conversation history is private to your signed-in account and
                    this CASPA module.</p>
            </aside>
        </div>
    </div>
    <div class="ci-toast" id="caspaToast"></div>

    <script>
        (function () {
            var form = document.getElementById('caspaForm'),
                input = document.getElementById('caspaMessage'),
                thread = document.getElementById('caspaThread'),
                sendBtn = document.getElementById('caspaSend'),
                sendLabel = document.getElementById('caspaSendLabel'),
                count = document.getElementById('caspaCount'),
                errBox = document.getElementById('caspaError'),
                errText = document.getElementById('caspaErrorText'),
                retryBtn = document.getElementById('caspaRetry'),
                toast = document.getElementById('caspaToast'),
                prompts = document.getElementById('caspaPrompts'),
                MAX = 8000, busy = false, lastMessage = '', toastTimer;

            var AI_ICON = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13z"/></svg>';
            var COPY_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';

            /* ---------- helpers ---------- */
            function esc(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
            function inline(s) {
                return s.replace(/`([^`]+)`/g, '<code>$1</code>').replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            }
            // Small, safe markdown renderer (input is escaped first)
            function render(text) {
                return text.split('```').map(function (part, i) {
                    if (i % 2 === 1) {
                        return '<pre><code>' + esc(part.replace(/^[a-zA-Z0-9_+-]*\n/, '').trim()) + '</code></pre>';
                    }
                    var out = '', list = null;
                    function close() { if (list) { out += '</' + list + '>'; list = null; } }
                    esc(part).split('\n').forEach(function (line) {
                        var m;
                        if ((m = line.match(/^\s*[-*]\s+(.*)/))) {
                            if (list !== 'ul') { close(); out += '<ul>'; list = 'ul'; }
                            out += '<li>' + inline(m[1]) + '</li>';
                        } else if ((m = line.match(/^\s*\d+[.)]\s+(.*)/))) {
                            if (list !== 'ol') { close(); out += '<ol>'; list = 'ol'; }
                            out += '<li>' + inline(m[1]) + '</li>';
                        } else if ((m = line.match(/^#{1,4}\s+(.*)/))) {
                            close(); out += '<h4>' + inline(m[1]) + '</h4>';
                        } else if (line.trim() === '') {
                            close();
                        } else {
                            close(); out += '<p>' + inline(line) + '</p>';
                        }
                    });
                    close();
                    return out;
                }).join('');
            }
            function timeNow() {
                return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            function showToast(msg) {
                toast.textContent = msg; toast.classList.add('show');
                clearTimeout(toastTimer); toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 1800);
            }
            function scrollDown() { thread.scrollTop = thread.scrollHeight; }
            function autosize() { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 260) + 'px'; }
            function updateState() {
                var len = input.value.length;
                count.textContent = len + ' / ' + MAX;
                count.className = 'ci-count' + (len > MAX * 0.95 ? ' max' : len > MAX * 0.8 ? ' warn' : '');
                sendBtn.disabled = busy || input.value.trim() === '';
            }
            function setBusy(state) {
                busy = state;
                sendBtn.classList.toggle('is-busy', state);
                sendLabel.textContent = state ? 'CASPA is thinking...' : 'Send to CASPA';
                updateState();
            }

            /* ---------- message rendering ---------- */
            function addUser(text) {
                var row = document.createElement('div');
                row.className = 'ci-row ci-row-user';
                row.innerHTML = '<div class="ci-avatar me">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</div>' +
                    '<div class="ci-bubble"><div class="ci-message ci-user"></div><div class="ci-meta">' + timeNow() + '</div></div>';
                row.querySelector('.ci-user').textContent = text;
                thread.appendChild(row); thread.classList.add('has-items'); scrollDown();
            }
            function addTyping() {
                var row = document.createElement('div');
                row.className = 'ci-row'; row.id = 'caspaTyping';
                row.innerHTML = '<div class="ci-avatar ai">' + AI_ICON + '</div>' +
                    '<div class="ci-bubble"><div class="ci-message ci-assistant ci-typing"><b></b><b></b><b></b><span>CASPA is preparing your response...</span></div></div>';
                thread.appendChild(row); scrollDown();
            }
            function removeTyping() { var t = document.getElementById('caspaTyping'); if (t) t.remove(); }
            function addAssistant(text) {
                var row = document.createElement('div');
                row.className = 'ci-row';
                row.innerHTML = '<div class="ci-avatar ai">' + AI_ICON + '</div>' +
                    '<div class="ci-bubble"><div class="ci-message ci-assistant ci-md"></div>' +
                    '<div class="ci-meta"><span>CASPA &middot; ' + timeNow() + '</span>' +
                    '<button type="button" class="ci-tool">' + COPY_ICON + '<span>Copy</span></button></div></div>';
                row.querySelector('.ci-md').innerHTML = render(text);
                row.querySelector('.ci-tool').addEventListener('click', function () {
                    var done = function () { showToast('Response copied to clipboard'); };
                    if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done); }
                    else { var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); ta.remove(); done(); }
                });
                thread.appendChild(row); scrollDown();
            }
            function showError(msg) { errText.textContent = msg; errBox.classList.add('show'); }
            function hideError() { errBox.classList.remove('show'); }

            /* ---------- sending ---------- */
            async function send(text, isRetry) {
                if (busy || !text.trim()) return;
                hideError(); setBusy(true); lastMessage = text;
                if (!isRetry) { addUser(text); input.value = ''; autosize(); }
                addTyping();
                try {
                    var response = await fetch("{{ route('caspa-chat.send') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json', 'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ message: text })
                    });
                    var data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Request failed. Please try again.');
                    removeTyping();
                    addAssistant(data.answer || 'No response returned.');
                    prompts.style.display = 'none';
                } catch (err) {
                    removeTyping();
                    showError(err.message || 'Something went wrong. Please try again.');
                } finally {
                    setBusy(false); input.focus();
                }
            }

            /* ---------- events ---------- */
            form.addEventListener('submit', function (e) { e.preventDefault(); send(input.value.trim(), false); });
            retryBtn.addEventListener('click', function () { send(lastMessage, true); });
            input.addEventListener('input', function () { autosize(); updateState(); });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); form.requestSubmit(); }
            });
            prompts.querySelectorAll('.ci-prompt').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    input.value = btn.querySelector('span').textContent;
                    autosize(); updateState(); input.focus();
                });
            });
            updateState();
        })();
    </script>
@endsection