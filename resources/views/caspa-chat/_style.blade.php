<style>
/* ============ CASPA Chat — design tokens ============ */
.ci-page{
    --ci-navy:#123B5D; --ci-navy-2:#176B87;
    --ci-ai:#5B3FA3; --ci-ai-hover:#4A3290; --ci-ai-deep:#3B2680;
    --ci-ai-soft:#F3EFFB; --ci-ai-softer:#FAF8FE; --ci-ai-border:#DDD3F2; --ci-ai-ring:rgba(91,63,163,.22);
    --ci-text:#25313C; --ci-muted:#64748B; --ci-line:#E2E8F0; --ci-line-2:#CBD5E1; --ci-bg:#F8FAFC;
    font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;color:var(--ci-text);
}
.ci-page *{box-sizing:border-box}

/* ============ Header ============ */
.ci-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px}
.ci-title{font-size:23px;font-weight:700;color:var(--ci-navy);margin:0;display:flex;align-items:center;gap:10px}
.ci-title-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--ci-ai);background:var(--ci-ai-soft);border:1px solid var(--ci-ai-border);padding:3px 9px;border-radius:20px;letter-spacing:.2px}
.ci-sub{font-size:13px;color:var(--ci-muted);margin:6px 0 0}
.ci-actions{display:flex;gap:9px;flex-wrap:wrap}

/* ============ Buttons ============ */
.ci-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:0 16px;border:1px solid var(--ci-line-2);border-radius:9px;background:#fff;color:#334155;text-decoration:none;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s,border-color .15s,box-shadow .15s,transform .05s}
.ci-btn:hover{background:#F1F5F9;border-color:#94A3B8}
.ci-btn:active{transform:translateY(1px)}
.ci-btn:focus-visible{outline:3px solid var(--ci-ai-ring);outline-offset:1px}
.ci-btn.primary{background:var(--ci-navy);color:#fff;border-color:var(--ci-navy)}
.ci-btn.primary:hover{background:#0E2E49}
.ci-btn.ai{background:var(--ci-ai);color:#fff;border-color:var(--ci-ai);box-shadow:0 2px 8px rgba(91,63,163,.28)}
.ci-btn.ai:hover{background:var(--ci-ai-hover);border-color:var(--ci-ai-hover)}
.ci-btn.ai:disabled{background:#A99BD0;border-color:#A99BD0;box-shadow:none;cursor:not-allowed}
.ci-btn.ghost-ai{background:var(--ci-ai-soft);color:var(--ci-ai);border-color:var(--ci-ai-border)}
.ci-btn.ghost-ai:hover{background:#EBE4F9;border-color:var(--ci-ai)}
.ci-btn.sm{min-height:30px;padding:0 11px;font-size:12px;border-radius:8px}
.ci-btn svg{width:15px;height:15px;flex:none}
.ci-spin{width:14px;height:14px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:ci-rot .7s linear infinite;display:none}
.ci-btn.is-busy .ci-spin{display:inline-block}
.ci-btn.is-busy .ci-send-icon{display:none}

/* ============ Layout / cards ============ */
.ci-layout{display:grid;grid-template-columns:minmax(0,1fr) 290px;gap:16px;align-items:start}
.ci-main,.ci-side{background:#fff;border:1px solid var(--ci-line-2);border-radius:15px;padding:18px}
.ci-side{position:sticky;top:16px}
.ci-side h3,.ci-main h3{font-size:15px;color:var(--ci-navy);margin:0 0 13px}
.ci-side h3{display:flex;align-items:center;gap:8px}

/* ============ Hero ============ */
.ci-hero{position:relative;overflow:hidden;padding:22px 20px;border-radius:13px;background:linear-gradient(125deg,#3B2680 0%,#5B3FA3 55%,#7A5CC8 100%);color:#fff;margin-bottom:15px}
.ci-hero::after{content:"";position:absolute;right:-50px;top:-60px;width:210px;height:210px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.2),transparent 70%)}
.ci-hero-ico{width:38px;height:38px;border-radius:11px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin-bottom:11px;backdrop-filter:blur(4px)}
.ci-hero-ico svg{width:20px;height:20px;color:#fff}
.ci-hero h2{font-size:18px;line-height:1.35;margin:0 0 6px;position:relative;z-index:1;max-width:560px}
.ci-hero p{font-size:12px;margin:0;opacity:.88;position:relative;z-index:1;max-width:560px}

/* ============ Chips ============ */
.ci-stat{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
.ci-chip{display:inline-flex;align-items:center;gap:6px;background:#F1F5F9;border-radius:20px;padding:6px 10px;font-size:11px;color:#475569}
.ci-chip i{width:6px;height:6px;border-radius:50%;background:#22C55E;display:inline-block}

/* ============ Suggested prompts ============ */
.ci-prompts{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
.ci-prompt{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--ci-ai-border);background:var(--ci-ai-softer);border-radius:18px;padding:8px 12px;font:500 12px Inter,sans-serif;color:var(--ci-ai-deep);cursor:pointer;transition:all .15s}
.ci-prompt svg{width:13px;height:13px;color:var(--ci-ai)}
.ci-prompt:hover{background:var(--ci-ai);color:#fff;border-color:var(--ci-ai);transform:translateY(-1px);box-shadow:0 4px 10px rgba(91,63,163,.22)}
.ci-prompt:hover svg{color:#fff}
.ci-prompt:focus-visible{outline:3px solid var(--ci-ai-ring);outline-offset:1px}

/* ============ Thread ============ */
.ci-thread{display:none;margin:16px 0 4px;padding-top:14px;border-top:1px dashed var(--ci-line-2);max-height:520px;overflow-y:auto;scroll-behavior:smooth}
.ci-thread.has-items{display:block}
.ci-row{display:flex;gap:10px;margin:14px 0;animation:ci-in .28s ease both}
.ci-row.ci-row-user{flex-direction:row-reverse}
.ci-avatar{flex:none;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}
.ci-avatar svg{width:16px;height:16px}
.ci-avatar.ai{background:var(--ci-ai);color:#fff;box-shadow:0 2px 6px rgba(91,63,163,.35)}
.ci-avatar.me{background:#E0ECF7;color:var(--ci-navy)}
.ci-bubble{min-width:0;max-width:calc(100% - 46px)}

/* Messages */
.ci-message{padding:13px 15px;border-radius:12px;font-size:13px;line-height:1.65;overflow-wrap:anywhere}
.ci-user{background:#EFF6FF;border:1px solid #DBEAFE;white-space:pre-wrap;border-top-right-radius:4px}
.ci-assistant{background:var(--ci-ai-softer);border:1px solid var(--ci-ai-border);border-left:3px solid var(--ci-ai);border-top-left-radius:4px}
.ci-md{white-space:normal}
[data-md]:not(.ci-md){white-space:pre-wrap}
.ci-md p{margin:0 0 8px}.ci-md p:last-child{margin-bottom:0}
.ci-md h4{margin:10px 0 6px;font-size:13px;color:var(--ci-ai-deep)}
.ci-md ul,.ci-md ol{margin:6px 0 8px;padding-left:20px}.ci-md li{margin:3px 0}
.ci-md strong{color:var(--ci-ai-deep)}
.ci-md code{background:#ECE6F8;color:var(--ci-ai-deep);padding:1px 5px;border-radius:5px;font:12px ui-monospace,SFMono-Regular,Menlo,monospace}
.ci-md pre{background:#1F1637;color:#EDE7FB;padding:12px 14px;border-radius:10px;overflow-x:auto;margin:8px 0}
.ci-md pre code{background:none;color:inherit;padding:0}
.ci-meta{display:flex;align-items:center;gap:10px;margin-top:7px;font-size:11px;color:var(--ci-muted)}
.ci-row-user .ci-meta{justify-content:flex-end}
.ci-tool{display:inline-flex;align-items:center;gap:5px;border:0;background:transparent;color:var(--ci-ai);font:600 11px Inter,sans-serif;padding:3px 7px;border-radius:6px;cursor:pointer}
.ci-tool:hover{background:var(--ci-ai-soft)}
.ci-tool svg{width:13px;height:13px}

/* Typing indicator */
.ci-typing{display:inline-flex;align-items:center;gap:5px;padding:14px 16px}
.ci-typing b{width:7px;height:7px;border-radius:50%;background:var(--ci-ai);opacity:.35;animation:ci-bounce 1.2s infinite ease-in-out}
.ci-typing b:nth-child(2){animation-delay:.15s}.ci-typing b:nth-child(3){animation-delay:.3s}
.ci-typing span{margin-left:6px;font-size:12px;color:var(--ci-ai)}

/* ============ Composer ============ */
.ci-compose{margin-top:14px}
.ci-compose-box{border:1px solid var(--ci-line-2);border-radius:13px;background:#fff;transition:border-color .15s,box-shadow .15s}
.ci-compose-box:focus-within{border-color:var(--ci-ai);box-shadow:0 0 0 4px var(--ci-ai-ring)}
.ci-compose textarea{display:block;width:100%;min-height:92px;max-height:260px;border:0;outline:0;background:transparent;border-radius:13px;padding:13px 14px 4px;font:13px/1.55 Inter,sans-serif;color:var(--ci-text);resize:none}
.ci-compose-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:8px 10px 10px 14px;flex-wrap:wrap}
.ci-foot-left{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.ci-count{font-size:11px;color:var(--ci-muted);font-variant-numeric:tabular-nums}
.ci-count.warn{color:#B45309}.ci-count.max{color:#B91C1C}
.ci-kbd{font-size:11px;color:var(--ci-muted)}
.ci-kbd kbd{background:#F1F5F9;border:1px solid var(--ci-line-2);border-bottom-width:2px;border-radius:5px;padding:0 5px;font:600 10px Inter,sans-serif;color:#475569}
.ci-muted{font-size:12px;color:var(--ci-muted)}
.ci-note{margin:9px 2px 0}

/* Error / loading */
.ci-error{display:none;align-items:center;justify-content:space-between;gap:10px;margin-top:10px;padding:10px 12px;border-radius:10px;background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C;font-size:12px}
.ci-error.show{display:flex}

/* ============ History sidebar ============ */
.ci-history a{display:block;padding:10px 10px;margin:0 -10px;border-radius:9px;border-bottom:0;color:#334155;text-decoration:none;font-size:12px;font-weight:500;transition:background .15s}
.ci-history a:hover{background:var(--ci-ai-soft);color:var(--ci-ai-deep)}
.ci-history a .ci-muted{margin-top:3px;font-weight:400}
.ci-history-empty{text-align:center;padding:18px 8px}
.ci-history-empty svg{width:26px;height:26px;color:var(--ci-ai-border);margin-bottom:6px}

/* Toast */
.ci-toast{position:fixed;left:50%;bottom:28px;transform:translate(-50%,20px);background:var(--ci-ai-deep);color:#fff;padding:9px 16px;border-radius:10px;font-size:12px;font-weight:600;opacity:0;pointer-events:none;transition:all .25s;z-index:9999;box-shadow:0 8px 24px rgba(59,38,128,.35)}
.ci-toast.show{opacity:1;transform:translate(-50%,0)}

/* Conversation page */
.ci-convo{max-width:860px;margin:0 auto}
.ci-convo .ci-main{padding:18px 20px}
.ci-convo-count{display:inline-flex;align-items:center;gap:6px;font-size:11px;color:var(--ci-ai);background:var(--ci-ai-soft);border:1px solid var(--ci-ai-border);padding:4px 10px;border-radius:20px;margin-bottom:6px}

/* Animations */
@keyframes ci-rot{to{transform:rotate(360deg)}}
@keyframes ci-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
@keyframes ci-bounce{0%,80%,100%{transform:translateY(0);opacity:.35}40%{transform:translateY(-5px);opacity:1}}
@media (prefers-reduced-motion:reduce){.ci-page *{animation:none!important;transition:none!important}}

@media(max-width:850px){.ci-layout{grid-template-columns:1fr}.ci-side{position:static}.ci-head{display:block}.ci-actions{margin-top:12px}.ci-bubble{max-width:calc(100% - 42px)}.ci-kbd{display:none}}
</style>