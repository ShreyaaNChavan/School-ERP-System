@extends('layouts.schoolnexa')

@section('content')
    @include('caspa-chat._style')

    <div class="ci-page ci-convo">
        <div class="ci-head">
            <div>
                <span class="ci-convo-count">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9-2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13z"/>
                    </svg>
                    CASPA &middot; {{ count($messages) }}
                    {{ \Illuminate\Support\Str::plural('message', count($messages)) }}
                </span>

                <h1 class="ci-title">{{ $conversation->title }}</h1>

                <p class="ci-sub">
                    Conversation history for {{ auth()->user()->name }}.
                </p>
            </div>

            <div class="ci-actions">
                <a class="ci-btn ghost-ai" href="{{ route('caspa-chat.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    Back to CASPA Chat Dashboard
                </a>
            </div>
        </div>

        <section class="ci-main">
            @foreach($messages as $message)
                @php
                    $isUser = $message->sender === 'user';
                @endphp

                <div class="ci-row {{ $isUser ? 'ci-row-user' : '' }}">

                    @if($isUser)
                        <div class="ci-avatar me">
                            {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="ci-avatar ai">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l1.9 5.6L19.5 9l-5.6 1.9L12 16.5l-1.9-5.6L4.5 9l5.6-1.4L12 2zm7 11l.9 2.6 2.6.9L19 20l-.9-2.6-2.6-.9 2.6-.9L19 13z"/>
                            </svg>
                        </div>
                    @endif

                    <div class="ci-bubble">
                        <div class="ci-message {{ $isUser ? 'ci-user' : 'ci-assistant' }}">
                            <div @unless($isUser) data-md @endunless>{{ $message->content }}</div>
                        </div>

                        <div class="ci-meta">
                            <span>
                                {{ $isUser ? 'You' : 'CASPA' }}
                                &middot;
                                {{ \Illuminate\Support\Carbon::parse($message->created_at)->format('d M Y, h:i A') }}
                            </span>

                            @unless($isUser)
                                <button type="button" class="ci-tool" data-copy>
                                    <svg viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                    <span>Copy</span>
                                </button>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    </div>

    <div class="ci-toast" id="caspaToast"></div>

    <script>
        (function () {
            function esc(s) {
                return s.replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            function inline(s) {
                return s.replace(/`([^`]+)`/g, '<code>$1</code>')
                    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            }

            function render(text) {
                return text.split('```').map(function (part, i) {
                    if (i % 2 === 1) {
                        return '<pre><code>' +
                            esc(part.replace(/^[a-zA-Z0-9_+-]*\n/, '').trim()) +
                            '</code></pre>';
                    }

                    var out = '', list = null;

                    function close() {
                        if (list) {
                            out += '</' + list + '>';
                            list = null;
                        }
                    }

                    esc(part).split('\n').forEach(function (line) {
                        var m;

                        if ((m = line.match(/^\s*[-*]\s+(.*)/))) {
                            if (list !== 'ul') {
                                close();
                                out += '<ul>';
                                list = 'ul';
                            }
                            out += '<li>' + inline(m[1]) + '</li>';

                        } else if ((m = line.match(/^\s*\d+[.)]\s+(.*)/))) {
                            if (list !== 'ol') {
                                close();
                                out += '<ol>';
                                list = 'ol';
                            }
                            out += '<li>' + inline(m[1]) + '</li>';

                        } else if ((m = line.match(/^#{1,4}\s+(.*)/))) {
                            close();
                            out += '<h4>' + inline(m[1]) + '</h4>';

                        } else if (line.trim() === '') {
                            close();

                        } else {
                            close();
                            out += '<p>' + inline(line) + '</p>';
                        }
                    });

                    close();
                    return out;
                }).join('');
            }

            var toast = document.getElementById('caspaToast');
            var timer;

            function showToast(msg) {
                toast.textContent = msg;
                toast.classList.add('show');

                clearTimeout(timer);

                timer = setTimeout(function () {
                    toast.classList.remove('show');
                }, 1800);
            }

            // Render assistant messages as formatted text; keep raw text for copy.
            document.querySelectorAll('[data-md]').forEach(function (el) {
                var raw = el.textContent;

                el.dataset.raw = raw;
                el.innerHTML = render(raw);
                el.classList.add('ci-md');
            });

            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var el = btn.closest('.ci-bubble').querySelector('[data-md]');
                    var text = el ? el.dataset.raw : '';

                    var done = function () {
                        showToast('Response copied to clipboard');
                    };

                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(text).then(done);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = text;

                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();

                        done();
                    }
                });
            });
        })();
    </script>
@endsection
