<?php

declare(strict_types=1);

function pp_fonts_link(): string
{
    return <<<'HTML'
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
HTML;
}

function pp_css_variables(): string
{
    return <<<'CSS'
  :root{
    --pp-bg:#12100a;
    --pp-bg-soft:#1c1810;
    --pp-card:#242018;
    --pp-card-hover:#2e2818;
    --pp-border:#4a4028;
    --pp-text:#faf6e8;
    --pp-muted:#b8a878;
    --pp-primary:#facc15;
    --pp-primary-glow:rgba(250,204,21,.35);
    --pp-accent:#eab308;
    --pp-accent-glow:rgba(234,179,8,.28);
    --pp-cta:#FFDF00;
    --pp-cta-hover:#fde047;
    --pp-cta-text:#1a1400;
    --pp-danger:#f43f5e;
    --pp-success:#34d399;
    --pp-radius:14px;
    --pp-radius-sm:10px;
    --pp-font-head:'Syne',system-ui,sans-serif;
    --pp-font-body:'DM Sans',system-ui,sans-serif;
  }
CSS;
}

function pp_footer_styles(): string
{
    return <<<'CSS'
  .pp-footer{
    text-align:center;
    padding:1.5rem 1rem 2rem;
    margin-top:2rem;
    font-size:12px;
    color:var(--pp-muted);
    border-top:1px solid var(--pp-border);
    letter-spacing:.04em;
  }
CSS;
}

function render_pp_footer(): void
{
    ?>
<footer class="pp-footer">© <?= (int) date('Y') ?> Comunidade Figurinhas da Copa · Copa 2026</footer>
<?php
}

/** Substitui rodapé Agência Job pelo rodapé Perfect Pay. */
function render_agency_footer(): void
{
    render_pp_footer();
}

function pp_app_shell_styles(): string
{
    return pp_css_variables() . pp_footer_styles() . <<<'CSS'
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    min-height:100vh;
    background:var(--pp-bg);
    background-image:
      radial-gradient(ellipse 80% 50% at 50% -20%, rgba(250,204,21,.14), transparent),
      radial-gradient(ellipse 60% 40% at 100% 50%, rgba(234,179,8,.08), transparent);
    font-family:var(--pp-font-body);
    color:var(--pp-text);
    line-height:1.6;
  }
  .pp-wrap{max-width:720px;margin:0 auto;padding:2rem 1.25rem 3rem}
  .pp-card{
    background:var(--pp-card);
    border:1px solid var(--pp-border);
    border-radius:var(--pp-radius);
    padding:1.75rem;
    box-shadow:0 20px 50px rgba(0,0,0,.25);
  }
  .pp-head{
    font-family:var(--pp-font-head);
    font-size:clamp(2rem,6vw,2.75rem);
    font-weight:800;
    letter-spacing:-.02em;
    line-height:1.1;
    color:var(--pp-text);
  }
  .pp-head em,.pp-head .accent{font-style:normal;color:var(--pp-primary)}
  .pp-label{
    display:block;
    font-size:11px;
    font-weight:600;
    letter-spacing:.12em;
    text-transform:uppercase;
    color:var(--pp-muted);
    margin-bottom:6px;
  }
  .pp-input,.pp-textarea{
    width:100%;
    padding:14px 16px;
    border-radius:var(--pp-radius-sm);
    border:1px solid var(--pp-border);
    background:var(--pp-bg-soft);
    color:var(--pp-text);
    font-family:var(--pp-font-body);
    font-size:15px;
    margin-bottom:1rem;
    transition:border-color .2s,box-shadow .2s;
  }
  .pp-input:focus,.pp-textarea:focus{
    outline:none;
    border-color:var(--pp-primary);
    box-shadow:0 0 0 3px var(--pp-primary-glow);
  }
  .pp-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    width:100%;
    padding:16px 24px;
    border:none;
    border-radius:var(--pp-radius-sm);
    background:linear-gradient(135deg,var(--pp-cta),var(--pp-cta-hover));
    color:var(--pp-cta-text);
    font-family:var(--pp-font-head);
    font-size:1.1rem;
    font-weight:700;
    letter-spacing:.02em;
    cursor:pointer;
    text-decoration:none;
    transition:transform .2s,box-shadow .2s;
    box-shadow:0 8px 28px rgba(250,204,21,.35);
  }
  .pp-btn:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(250,204,21,.5)}
  .pp-btn-ghost{
    background:transparent;
    color:var(--pp-muted);
    box-shadow:none;
    border:1px solid var(--pp-border);
    font-family:var(--pp-font-body);
    font-size:14px;
    font-weight:600;
  }
  .pp-btn-ghost:hover{color:var(--pp-primary);border-color:var(--pp-primary)}
  .pp-hint{font-size:13px;color:var(--pp-muted);margin-top:-4px;margin-bottom:1rem;line-height:1.5}
  .pp-err{
    background:rgba(244,63,94,.12);
    border:1px solid rgba(244,63,94,.4);
    color:#fda4af;
    padding:12px 14px;
    border-radius:var(--pp-radius-sm);
    font-size:13px;
    margin-bottom:1rem;
  }
  .pp-flash{
    background:rgba(250,204,21,.1);
    border:1px solid rgba(250,204,21,.4);
    color:var(--pp-text);
    padding:12px 14px;
    border-radius:var(--pp-radius-sm);
    font-size:13px;
    margin-bottom:1rem;
  }
  .pp-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:6px 14px;
    border-radius:999px;
    background:rgba(250,204,21,.12);
    border:1px solid rgba(250,204,21,.4);
    color:var(--pp-primary);
    font-size:11px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
    margin-bottom:1rem;
  }
CSS;
}

function pp_nav_styles(): string
{
    return <<<'CSS'
  .page-nav{
    display:flex;align-items:center;justify-content:space-between;gap:8px;
    max-width:720px;margin:0 auto 1.5rem;padding:0 1.25rem;
  }
  .page-nav-btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:10px 14px;border-radius:var(--pp-radius-sm);
    background:var(--pp-card);border:1px solid var(--pp-border);
    color:var(--pp-muted);font-size:13px;font-weight:600;text-decoration:none;
    transition:all .2s;flex:1;max-width:140px;
  }
  .page-nav-btn:hover:not(.page-nav-btn-disabled){
    border-color:var(--pp-primary);color:var(--pp-primary);background:var(--pp-card-hover);
  }
  .page-nav-prev{justify-content:flex-start}
  .page-nav-next{justify-content:flex-end;flex-direction:row-reverse}
  .page-nav-btn-disabled{opacity:.35;cursor:not-allowed;pointer-events:none;justify-content:center}
  .page-nav-hint{display:block;font-size:10px;font-weight:500;color:var(--pp-muted);text-transform:uppercase;margin-top:2px}
  .page-nav-current{
    flex-shrink:0;padding:8px 16px;border-radius:999px;
    background:rgba(250,204,21,.15);border:1px solid rgba(250,204,21,.45);
    color:var(--pp-primary);font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  }
  @media(max-width:480px){
    .page-nav-hint{display:none}
    .page-nav-btn{padding:10px 12px;font-size:12px;max-width:110px}
  }
CSS;
}

function pp_admin_shell_styles(): string
{
    return pp_app_shell_styles() . pp_nav_styles() . <<<'CSS'
  .wrap{max-width:900px;margin:0 auto;padding:2rem 1.25rem 3rem}
  header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem}
  .admin-title{font-family:var(--pp-font-head);font-size:2rem;font-weight:800;letter-spacing:-.02em}
  .admin-title span{color:var(--pp-primary)}
  .links a{color:var(--pp-muted);margin-left:1rem;font-size:14px;text-decoration:none;font-weight:500}
  .links a:hover{color:var(--pp-primary)}
  .card{background:var(--pp-card);border:1px solid var(--pp-border);border-radius:var(--pp-radius);padding:1.5rem;margin-bottom:1.5rem}
  .card h2{font-family:var(--pp-font-head);font-size:1.25rem;margin-bottom:1rem;color:var(--pp-primary);font-weight:700}
  label{display:block;font-size:11px;color:var(--pp-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.08em;font-weight:600}
  input,textarea,select{width:100%;padding:12px 14px;border-radius:var(--pp-radius-sm);border:1px solid var(--pp-border);background:var(--pp-bg-soft);color:var(--pp-text);margin-bottom:1rem;font-family:var(--pp-font-body);font-size:14px}
  input:focus,textarea:focus{outline:none;border-color:var(--pp-primary);box-shadow:0 0 0 3px var(--pp-primary-glow)}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
  @media(max-width:600px){.row{grid-template-columns:1fr}}
  .btn{
    width:auto;padding:12px 22px;border:none;border-radius:var(--pp-radius-sm);
    background:linear-gradient(135deg,var(--pp-cta),var(--pp-cta-hover));color:var(--pp-cta-text);
    font-family:var(--pp-font-head);font-size:1rem;font-weight:700;letter-spacing:.02em;cursor:pointer;
    box-shadow:0 6px 20px rgba(250,204,21,.3);
  }
  .btn-danger{background:var(--pp-danger);color:#fff;box-shadow:none}
  table{width:100%;border-collapse:collapse;font-size:14px}
  th,td{padding:10px 8px;text-align:left;border-bottom:1px solid var(--pp-border)}
  th{color:var(--pp-muted);font-size:11px;text-transform:uppercase;letter-spacing:.06em}
  .flash{padding:12px 14px;border-radius:var(--pp-radius-sm);margin-bottom:1rem;background:rgba(250,204,21,.1);border:1px solid rgba(250,204,21,.4);color:var(--pp-text);font-size:14px}
  .pwd-box{background:var(--pp-bg-soft);padding:12px;border-radius:var(--pp-radius-sm);font-family:monospace;color:var(--pp-accent);margin-top:8px;word-break:break-all;border:1px solid var(--pp-border)}
  .hint{font-size:12px;color:var(--pp-muted);margin-top:-8px;margin-bottom:1rem;line-height:1.5}
  .page-nav{max-width:900px}
  .smtp-ok{color:var(--pp-success)!important}
CSS;
}
