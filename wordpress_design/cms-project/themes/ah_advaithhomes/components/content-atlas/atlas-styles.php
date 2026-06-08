<?php defined( 'ABSPATH' ) || exit; ?>
<style>
.atlas-hero{background:linear-gradient(135deg,rgba(255,255,255,.95),rgba(255,249,230,.9)),repeating-linear-gradient(90deg,rgba(183,121,31,.04) 0,rgba(183,121,31,.04) 1px,transparent 1px,transparent 56px);border-bottom:1px solid var(--border)}
.atlas-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:24px}
.atlas-card{background:var(--surface);border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow-sm);padding:22px}
.atlas-card h3,.atlas-card h4{margin:0 0 12px}
.atlas-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px}
.atlas-summary-card{background:linear-gradient(180deg,#fff,#fff8e8);border:1px solid rgba(183,121,31,.18);border-radius:16px;padding:18px}
.atlas-summary-card strong{display:block;font-size:2rem;font-family:var(--font-display);line-height:1;margin-bottom:8px;color:var(--slate-900)}
.atlas-list{list-style:none;padding:0;margin:0;display:grid;gap:12px}
.atlas-list li{padding:12px 14px;border:1px solid var(--border);border-radius:12px;background:#fff}
.atlas-label{display:inline-block;padding:5px 10px;border-radius:999px;background:var(--client-color-50);color:var(--client-color-800);font-size:.74rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase}
.atlas-muted{color:var(--text-secondary);font-size:.92rem}
.atlas-link,.atlas-muted a{color:var(--client-color-800);text-decoration:underline;text-underline-offset:2px;word-break:break-word}
.atlas-link:hover,.atlas-muted a:hover{color:var(--client-color-900)}
.atlas-kv{display:grid;grid-template-columns:160px 1fr;gap:10px 16px;margin:0}
.atlas-kv dt{color:var(--text-muted);font-weight:600}
.atlas-kv dd{margin:0;color:var(--text-primary)}
.atlas-two-col{display:grid;grid-template-columns:1fr 1fr;gap:24px}
.atlas-three-col{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.atlas-mini-card{padding:16px;border:1px solid var(--border);border-radius:14px;background:linear-gradient(180deg,#fff,#fffcf4)}
.atlas-mini-card p:last-child,.atlas-card p:last-child{margin-bottom:0}
.atlas-table{width:100%;border-collapse:collapse;font-size:.92rem}
.atlas-table th,.atlas-table td{text-align:left;padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:top}
.atlas-table th{color:var(--text-muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em}
@media(max-width:980px){.atlas-two-col,.atlas-three-col{grid-template-columns:1fr}.atlas-kv{grid-template-columns:1fr;gap:6px}}
</style>
