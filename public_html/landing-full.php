<?php
$heroVideoEmbed = $S['home_video_embed'] ?? 'https://www.youtube.com/embed/yskrod-EXeQ';
$heroVideoEmbedSafe = htmlspecialchars($heroVideoEmbed, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comunidade Perfect Pay — Copa do Mundo 2026</title>
<link rel="icon" href="/favicon.jpg" type="image/jpeg">
<link rel="shortcut icon" href="/favicon.jpg" type="image/jpeg">
<link rel="apple-touch-icon" href="/favicon.jpg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
  :root{
    --br-green:#ca8a04;
    --br-yellow:#FFDF00;
    --br-blue:#1a1408;
    --br-yellow-dim:#b8860b;
    --accent:var(--br-yellow);
    --accent-2:var(--br-green);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{overflow-x:hidden;-webkit-text-size-adjust:100%}
  body{background:#12100a;font-family:'DM Sans',system-ui,sans-serif;color:#faf6e8;overflow-x:hidden;width:100%}
  img,video,iframe{max-width:100%}

  /* ===== HERO FULLSCREEN ===== */
  .hero-full{position:relative;width:100%;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .hero-bg{position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;filter:brightness(0.35) saturate(1.2);z-index:0}
  .hero-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(180deg,rgba(202,138,4,.25) 0%,rgba(250,204,21,.12) 42%,rgba(18,16,10,.9) 72%,#12100a 100%);z-index:1}
  .hero-content{position:relative;z-index:2;text-align:center;padding:clamp(1rem,4vw,2rem);max-width:900px;width:100%;min-width:0;display:flex;flex-direction:column;align-items:center}

  .badge-hero{display:inline-flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;max-width:100%;background:rgba(0,151,57,0.2);border:1px solid rgba(255,223,0,0.6);color:var(--br-yellow);font-size:clamp(10px,2.5vw,12px);font-weight:700;letter-spacing:clamp(1px,1vw,4px);text-transform:uppercase;padding:8px clamp(12px,4vw,24px);border-radius:2px;margin-bottom:1.5rem;backdrop-filter:blur(10px);text-align:center;line-height:1.4}
  .badge-hero i{font-size:16px}

  .hero-content h1{font-family:'Syne',sans-serif;font-size:clamp(28px,8vw,72px);letter-spacing:clamp(0px,0.5vw,3px);line-height:1.05;color:#fff;margin-bottom:.5rem;text-shadow:0 4px 30px rgba(0,0,0,0.8);word-break:break-word;hyphens:auto}
  .hero-content h1 .gold{color:var(--br-yellow);text-shadow:0 0 40px rgba(255,223,0,0.5),0 4px 20px rgba(0,0,0,0.5)}
  .hero-content h1 .green{color:var(--br-green);text-shadow:0 0 30px rgba(0,151,57,0.6),0 4px 20px rgba(0,0,0,0.5)}
  .hero-content h1 .blue{color:#4da3ff;text-shadow:0 0 30px rgba(0,39,118,0.8),0 4px 20px rgba(0,0,0,0.5)}

  .hero-subtitle{font-family:'Syne',sans-serif;font-size:clamp(16px,4.5vw,28px);letter-spacing:clamp(2px,1.5vw,8px);color:var(--br-yellow);margin-bottom:1rem;opacity:.9;text-shadow:0 2px 10px rgba(0,0,0,0.5);padding:0 .5rem;line-height:1.3;word-break:break-word}

  .hero-video{position:relative;width:100%;max-width:560px;margin:0 auto 1.5rem;aspect-ratio:16/9;border-radius:8px;overflow:hidden;border:2px solid rgba(0,151,57,0.45);box-shadow:0 8px 32px rgba(0,0,0,0.55)}
  .hero-video iframe{position:absolute;inset:0;width:100%;height:100%;border:0;display:block}

  .hero-desc{font-size:clamp(14px,2.5vw,18px);color:#ccc;max-width:600px;width:100%;margin:0 auto 2rem;line-height:1.7;text-shadow:0 2px 10px rgba(0,0,0,0.5);padding:0 clamp(.25rem,2vw,.5rem)}

  .btn-hero{display:inline-flex;align-items:center;justify-content:center;gap:12px;width:100%;max-width:420px;background:linear-gradient(135deg,#FFDF00,#fde047);color:#1a1400;font-family:'Syne',sans-serif;font-size:clamp(18px,4vw,28px);letter-spacing:.02em;padding:clamp(16px,4vw,22px) clamp(24px,8vw,48px);border-radius:14px;border:2px solid #ca8a04;cursor:pointer;text-decoration:none;box-shadow:0 12px 40px rgba(250,204,21,.45);transition:all .3s;position:relative;overflow:hidden;font-weight:700;text-align:center;line-height:1.2}
  .btn-hero::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.4),transparent);transition:left .6s}
  .btn-hero:hover::before{left:100%}
  .btn-hero:hover{box-shadow:0 12px 50px rgba(255,223,0,0.5),0 0 60px rgba(0,151,57,0.35);transform:translateY(-3px)}
  .btn-hero:active{transform:scale(0.98)}

  .hero-stats{display:flex;justify-content:center;flex-wrap:wrap;gap:clamp(1rem,4vw,3rem);margin-top:3rem;padding-top:2rem;border-top:1px solid rgba(0,151,57,0.4);width:100%}
  .hero-stat{text-align:center;flex:1 1 100px;min-width:0;padding:0 .5rem}
  .hero-stat strong{display:block;font-family:'Syne',sans-serif;font-size:clamp(28px,5vw,40px);color:var(--br-yellow);text-shadow:0 0 20px rgba(255,223,0,0.35)}
  .hero-stat span{font-size:12px;color:#aaa;letter-spacing:2px;text-transform:uppercase;font-weight:600}

  .scroll-down{position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);z-index:2;color:var(--br-yellow);font-size:28px;animation:bounce 2s infinite;cursor:pointer}
  @keyframes bounce{0%,20%,50%,80%,100%{transform:translateX(-50%) translateY(0)}40%{transform:translateX(-50%) translateY(-15px)}60%{transform:translateX(-50%) translateY(-8px)}}

  /* ===== SECTIONS ===== */
  .lp{max-width:100%;width:100%;background:#12100a;position:relative}
  .section{padding:5rem 1.5rem;max-width:1200px;margin:0 auto}
  .section-title{font-family:'Syne',sans-serif;font-size:clamp(22px,5vw,42px);letter-spacing:clamp(1px,0.5vw,3px);color:#fff;margin-bottom:2rem;text-align:center;padding:0 .5rem}
  .section-title .gold{color:var(--br-yellow)}
  .section-title .green{color:var(--br-green)}
  .section-subtitle{text-align:center;color:#888;font-size:clamp(13px,3.5vw,15px);margin-bottom:3rem;max-width:600px;margin-left:auto;margin-right:auto;line-height:1.6;padding:0 1rem}

  /* ===== RARITY ===== */
  .rarity-section{background:#0d0d0d;padding:4rem 1.5rem;border-top:1px solid #1a1a1a;border-bottom:1px solid #1a1a1a}
  .rarity-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;max-width:1000px;margin:0 auto}
  .rarity-card{background:#242018;border:1px solid #222;border-radius:8px;padding:24px 16px;text-align:center;transition:all .3s;position:relative;overflow:hidden}
  .rarity-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:var(--rarity-color)}
  .rarity-card:hover{border-color:var(--rarity-color);transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,0.3)}
  .rarity-card .rarity-icon{font-size:36px;margin-bottom:12px}
  .rarity-card .rarity-name{font-family:'Syne';font-size:20px;color:#fff;letter-spacing:2px}
  .rarity-card .rarity-chance{font-size:12px;color:#888;margin-top:6px;font-weight:500}
  .rarity-card .rarity-price{font-size:14px;color:var(--rarity-color);font-weight:700;margin-top:8px;font-family:'Syne';font-size:18px}
  .rarity-purple{--rarity-color:#9b59b6}
  .rarity-bronze{--rarity-color:#cd7f32}
  .rarity-silver{--rarity-color:#c0c0c0}
  .rarity-gold{--rarity-color:var(--br-yellow)}

  /* ===== URGENCY ===== */
  .urgency{background:#0d0d0d;border:2px solid var(--br-green);border-radius:8px;padding:2rem;margin:0 auto 2rem;max-width:700px;text-align:center;position:relative;overflow:hidden;box-shadow:0 0 30px rgba(0,151,57,0.15)}
  .urgency::before{content:'';position:absolute;top:0;left:0;right:0;bottom:0;background:radial-gradient(circle at center,rgba(255,223,0,0.06) 0%,transparent 70%);pointer-events:none}
  .urgency>p{font-size:14px;color:#aaa;margin-bottom:1.2rem;font-weight:600}
  .urgency>p i{color:var(--br-yellow);margin-right:8px}
  .timer{display:flex;justify-content:center;gap:1rem;margin-bottom:1.5rem}
  .timer-box{background:#242018;border:1px solid #333;border-radius:6px;padding:14px 20px;text-align:center;min-width:80px}
  .timer-box strong{display:block;font-family:'Syne';font-size:36px;color:var(--br-yellow);line-height:1;text-shadow:0 0 20px rgba(255,223,0,0.4)}
  .timer-box span{font-size:11px;color:#888;text-transform:uppercase;letter-spacing:2px;font-weight:600}
  .timer-sep{font-family:'Syne';font-size:28px;color:var(--br-yellow);align-self:center}

  .btn-main{display:inline-flex;align-items:center;justify-content:center;gap:10px;background:var(--br-yellow);color:#1a1400;font-family:'Syne';font-size:clamp(15px,4vw,22px);letter-spacing:1px;padding:16px clamp(20px,5vw,40px);border-radius:8px;border:2px solid var(--br-green);cursor:pointer;text-decoration:none;transition:all .2s;font-weight:700;max-width:100%;text-align:center;flex-wrap:wrap}
  .btn-main:hover{background:#ffe566;transform:scale(1.03);box-shadow:0 6px 30px rgba(0,151,57,0.35)}

  /* ===== FEATURES ===== */
  .features-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;max-width:900px;margin:0 auto}
  .feat{background:#242018;border:1px solid #222;border-radius:8px;padding:20px;display:flex;align-items:flex-start;gap:14px;transition:all .25s}
  .feat:hover{border-color:var(--br-yellow);background:#1a1a1a;transform:translateX(4px)}
  .feat-icon{color:var(--br-yellow);font-size:26px;margin-top:2px;flex-shrink:0}
  .feat-text{font-size:15px;color:#bbb;line-height:1.6}
  .feat-text strong{color:#fff;display:block;font-size:16px;margin-bottom:4px;font-weight:700}

  /* ===== TESTIMONIALS ===== */
  .testimonials{background:#0d0d0d;padding:4rem 1.5rem;border-top:1px solid #1a1a1a;border-bottom:1px solid #1a1a1a}
  .testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;max-width:1100px;margin:0 auto}
  .testi{background:#242018;border:1px solid #222;border-radius:8px;padding:24px;position:relative;transition:all .3s}
  .testi:hover{border-color:var(--br-yellow)}
  .testi::before{content:'"';position:absolute;top:12px;left:16px;font-family:'Syne';font-size:56px;color:var(--br-yellow);opacity:.25;line-height:1}
  .testi p{font-size:15px;color:#ccc;line-height:1.7;font-style:italic;padding-left:24px;padding-top:16px}
  .testi strong{display:block;font-size:13px;color:var(--br-yellow);margin-top:16px;font-style:normal;font-weight:700;padding-left:24px}
  .testi-rating{color:var(--br-yellow);font-size:14px;margin-top:8px;padding-left:24px;letter-spacing:2px}

  /* ===== STEPS ===== */
  .steps-section{background:#12100a;padding:4rem 1.5rem}
  .steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;max-width:1000px;margin:0 auto}
  .step-card{background:#242018;border:1px solid #222;border-radius:8px;padding:28px 20px;text-align:center;transition:all .3s;position:relative}
  .step-card:hover{border-color:var(--br-yellow);transform:translateY(-5px)}
  .step-num{display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;background:linear-gradient(135deg,var(--br-green),#00b347);color:#fff;font-family:'Syne';font-size:24px;border-radius:50%;font-weight:700;margin-bottom:16px;box-shadow:0 0 20px rgba(0,151,57,0.4);border:2px solid var(--br-yellow)}
  .step-card strong{color:#fff;font-size:17px;display:block;margin-bottom:8px}
  .step-card p{color:#888;font-size:14px;line-height:1.5}
  .step-arrow{position:absolute;top:50%;right:-18px;transform:translateY(-50%);color:var(--br-yellow);font-size:24px;z-index:2}

  /* ===== FOOTER CTA ===== */
  .footer-cta{position:relative;padding:5rem 1.5rem;text-align:center;overflow:hidden;min-height:600px;display:flex;align-items:center;justify-content:center}
  .footer-cta-bg{position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;filter:brightness(0.25) saturate(1.3);z-index:0}
  .footer-cta-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(180deg,#12100a 0%,rgba(202,138,4,.2) 25%,rgba(250,204,21,.1) 50%,rgba(18,16,10,.85) 80%,#12100a 100%);z-index:1}
  .footer-cta-content{position:relative;z-index:2;max-width:700px;width:100%;padding:0 1rem;box-sizing:border-box}
  .footer-cta h2{font-family:'Syne';font-size:clamp(24px,7vw,52px);color:#fff;letter-spacing:clamp(1px,0.5vw,3px);margin-bottom:.8rem;line-height:1.1}
  .footer-cta h2 .gold{color:var(--br-yellow)}
  .footer-cta>p{color:#bbb;font-size:17px;margin-bottom:2.5rem;line-height:1.7}

  .price-tag{display:inline-block;background:rgba(20,20,20,0.9);border:2px solid var(--br-yellow);border-radius:8px;padding:18px clamp(16px,5vw,40px);margin-bottom:2rem;position:relative;backdrop-filter:blur(10px);max-width:100%;box-sizing:border-box}
  .price-tag::before{content:'LANÇAMENTO';position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(90deg,var(--br-green),var(--br-yellow));color:#002776;font-size:10px;font-weight:800;letter-spacing:3px;padding:4px 16px;border-radius:2px}
  .price-tag s{color:#666;font-size:18px;display:block;margin-bottom:6px}
  .price-tag strong{display:block;font-family:'Syne';font-size:48px;color:var(--br-yellow);line-height:1;text-shadow:0 0 30px rgba(255,223,0,0.5)}
  .price-tag span{font-size:13px;color:#888;font-weight:600}

  .btn-sec{display:inline-block;background:transparent;color:var(--br-yellow);font-size:15px;font-weight:600;padding:12px 28px;border:1px solid var(--br-green);border-radius:4px;cursor:pointer;text-decoration:none;margin:8px;transition:all .2s}
  .btn-sec:hover{background:rgba(0,151,57,0.15);border-color:var(--br-yellow);transform:scale(1.02)}

  .seal{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:10px;color:#888;font-size:clamp(11px,3vw,13px);margin-top:2rem;font-weight:500;padding:0 .5rem;line-height:1.5;text-align:center}
  .footer-cta .btn-hero.footer-cta-btn{font-size:clamp(16px,4.5vw,24px)!important;padding:16px clamp(16px,5vw,32px)!important;width:100%;max-width:100%}
  .seal i{color:var(--br-yellow);font-size:20px}

  .pulse{animation:pulse 2.5s infinite}
  @keyframes pulse{0%,100%{box-shadow:0 8px 40px rgba(255,223,0,0.4),0 0 60px rgba(255,223,0,0.2)}50%{box-shadow:0 12px 50px rgba(255,223,0,0.6),0 0 80px rgba(255,223,0,0.3)}}

  .shimmer{position:relative;overflow:hidden}
  .shimmer::after{content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.1),transparent);animation:shimmer 3s infinite}
  @keyframes shimmer{0%{left:-100%}100%{left:200%}}

  .divider{height:1px;background:linear-gradient(90deg,transparent,#1a1a1a,transparent);margin:0}

  .agency-footer{text-align:center;padding:1.5rem 1rem 2rem;font-size:13px;color:#555;background:#12100a;border-top:1px solid #1a1a1a}
  .agency-footer a{color:#888;text-decoration:none;font-weight:600}
  .agency-footer a:hover{color:var(--br-yellow)}

  @media(max-width:1024px){
    .steps-grid{grid-template-columns:repeat(2,1fr);gap:16px}
    .step-arrow{display:none}
    .testi-grid{grid-template-columns:repeat(2,1fr)}
    .rarity-grid{grid-template-columns:repeat(2,1fr)}
  }
  @media(max-width:768px){
    .hero-full{min-height:100dvh;padding:4rem 0 3rem}
    .hero-content{padding:1rem}
    .hero-content h1{font-size:clamp(24px,8.5vw,38px)}
    .badge-hero{margin-bottom:1rem}
    .hero-video{margin-bottom:1rem}
    .hero-desc{margin-bottom:1.25rem}
    .btn-hero{max-width:100%}
    .hero-stats{margin-top:1.75rem;padding-top:1.25rem;gap:.75rem 1rem}
    .hero-stat{flex:1 1 calc(33.333% - 12px)}
    .hero-stat span{font-size:10px;letter-spacing:1px}
    .scroll-down{bottom:1rem;font-size:22px}
    .section,.rarity-section,.testimonials,.steps-section{padding:3rem 1rem}
    .section-title{margin-bottom:1.25rem}
    .section-subtitle{margin-bottom:2rem}
    .rarity-grid{gap:10px}
    .rarity-card{padding:16px 12px}
    .rarity-card .rarity-name{font-size:17px;letter-spacing:1px}
    .urgency{padding:1.25rem 1rem;margin-left:auto;margin-right:auto;width:100%}
    .timer{flex-wrap:nowrap;justify-content:center;gap:.35rem;width:100%}
    .timer-box{min-width:0;flex:1 1 0;padding:8px 6px}
    .timer-box strong{font-size:clamp(22px,6vw,30px)}
    .timer-sep{font-size:20px;flex-shrink:0}
    .btn-main{width:100%}
    .features-grid{grid-template-columns:1fr}
    .feat-text{font-size:14px}
    .testi-grid{grid-template-columns:1fr}
    .steps-grid{grid-template-columns:1fr}
    .step-card{padding:22px 16px}
    .footer-cta{min-height:auto;padding:3.5rem 1rem}
    .footer-cta>p{font-size:15px;margin-bottom:1.5rem}
    .price-tag{width:100%;padding:14px 16px}
    .price-tag strong{font-size:clamp(28px,9vw,44px)}
    .price-tag span{font-size:12px;line-height:1.4}
    .btn-sec{display:block;width:100%;max-width:100%;margin:8px 0;box-sizing:border-box;text-align:center}
    .seal{flex-direction:column}
  }
  @media(max-width:480px){
    .hero-stat{flex:1 1 100%}
    .hero-stats{gap:1rem}
    .rarity-grid{grid-template-columns:1fr}
    .testi-grid{grid-template-columns:1fr}
    .rarity-card .rarity-name{font-size:18px}
  }
  @media(max-width:360px){
    .hero-content h1{font-size:22px}
    .timer-sep{display:none}
    .timer{gap:6px}
  }
</style>
</head>
<body>

<!-- HERO FULLSCREEN -->
<div class="hero-full">
  <img src="/ChatGPT-Image-16-de-mai.-de-2026_-13_07_10.webp" alt="Estádio Copa 2026" class="hero-bg">
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="badge-hero"><i class="ti ti-users"></i> Comunidade Exclusiva FIFA World Cup 2026</div>
    <h1>COMUNIDADE <span class="gold">PERFECT PAY</span><br><span class="green">VIP</span> <span class="blue">2026</span></h1>
    <div class="hero-video">
      <iframe src="<?= $heroVideoEmbedSafe ?>" title="Vídeo Comunidade Perfect Pay" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
    </div>
    <p class="hero-desc">Complete seu álbum em apenas 24hrs com esse ebook completo de figurinhas da Copa do Mundo 2026! Com as 980 figurinhas, todas organizadas, prontas para imprimir e colar no seu álbum.</p>
    <a href="https://checkout.perfectpay.com.br/pay/PPU38CQC6TK" class="btn-hero pulse shimmer"><i class="ti ti-trophy"></i> ENTRAR NA COMUNIDADE</a>
    <div class="hero-stats">
      <div class="hero-stat"><strong>+500</strong><span>Membros VIP</span></div>
      <div class="hero-stat"><strong>+1000</strong><span>Figurinhas na Plataforma</span></div>
      <div class="hero-stat"><strong>80+</strong><span>Legend Exclusivas</span></div>
    </div>
  </div>
  <div class="scroll-down"><i class="ti ti-chevron-down"></i></div>
</div>

<div class="lp">

  <!-- RARITY -->
  <div class="rarity-section">
    <div class="section-title">Sistema de <span class="gold">Raridade</span></div>
    <p class="section-subtitle">Conheça as raridades da Copa 2026 — Legend Lilás, Bronze, Prata e Ouro disponíveis na plataforma VIP</p>
    <div class="rarity-grid">
      <div class="rarity-card rarity-purple">
        <div class="rarity-icon">💜</div>
        <div class="rarity-name">LEGEND LILÁS</div>
        <div class="rarity-chance">1 em 190 pacotes</div>
        <div class="rarity-price">DISPONÍVEL VIP</div>
      </div>
      <div class="rarity-card rarity-bronze">
        <div class="rarity-icon">🥉</div>
        <div class="rarity-name">LEGEND BRONZE</div>
        <div class="rarity-chance">1 em 317 pacotes</div>
        <div class="rarity-price">DISPONÍVEL VIP</div>
      </div>
      <div class="rarity-card rarity-silver">
        <div class="rarity-icon">🥈</div>
        <div class="rarity-name">LEGEND PRATA</div>
        <div class="rarity-chance">1 em 900 pacotes</div>
        <div class="rarity-price">DISPONÍVEL VIP</div>
      </div>
      <div class="rarity-card rarity-gold">
        <div class="rarity-icon">🥇</div>
        <div class="rarity-name">LEGEND OURO</div>
        <div class="rarity-chance">1 em 1.900 pacotes</div>
        <div class="rarity-price">DISPONÍVEL VIP</div>
      </div>
    </div>
  </div>

  <!-- URGENCY -->
  <div class="section" style="padding-bottom:2rem">
    <div class="urgency">
      <p><i class="ti ti-clock"></i> Oferta de lançamento da comunidade — expira em:</p>
      <div class="timer">
        <div class="timer-box"><strong id="t-h">23</strong><span>horas</span></div>
        <div class="timer-sep">:</div>
        <div class="timer-box"><strong id="t-m">47</strong><span>min</span></div>
        <div class="timer-sep">:</div>
        <div class="timer-box"><strong id="t-s">00</strong><span>seg</span></div>
      </div>
      <a href="https://checkout.perfectpay.com.br/pay/PPU38CQC6TK" class="btn-main"><i class="ti ti-lock-access"></i> ENTRAR NA COMUNIDADE</a>
    </div>
  </div>

  <!-- FEATURES -->
  <div class="section">
    <div class="section-title">Benefícios <span class="gold">Exclusivos</span></div>
    <div class="features-grid">
      <div class="feat"><i class="ti ti-file-text feat-icon"></i><div class="feat-text"><strong>Acesso a Todos os PDFs</strong>Mais de 1.000 figurinhas em alta resolução — 980 do álbum + 80+ Legend exclusivas na plataforma</div></div>
      <div class="feat"><i class="ti ti-star feat-icon"></i><div class="feat-text"><strong>Legend Exclusivas</strong>Messi, Cristiano Ronaldo, Vini Jr, Haaland, Mbappé e todas as raridades disponíveis para membros VIP</div></div>
      <div class="feat"><i class="ti ti-sparkles feat-icon"></i><div class="feat-text"><strong>Holográficas na Plataforma</strong>Edições especiais em PDF — conteúdo exclusivo que não encontra em lugar nenhum</div></div>
      <div class="feat"><i class="ti ti-video feat-icon"></i><div class="feat-text"><strong>Bastidores do Canal</strong>Saiba tudo sobre figurinhas com conteúdo exclusivo Perfect Pay — dicas, análises e identificação de falsas</div></div>
      <div class="feat"><i class="ti ti-cpu feat-icon"></i><div class="feat-text"><strong>Scans em 600 DPI</strong>Qualidade profissional — cada figurinha em alta definição dentro da plataforma</div></div>
      <div class="feat"><i class="ti ti-shield feat-icon"></i><div class="feat-text"><strong>Acesso Vitalício à Plataforma</strong>Entre na comunidade e mantenha acesso permanente com atualizações pós-Copa</div></div>
      <div class="feat"><i class="ti ti-lock feat-icon"></i><div class="feat-text"><strong>Atualizações Exclusivas</strong>Novos PDFs adicionados constantemente após convocações e lançamentos oficiais</div></div>
      <div class="feat"><i class="ti ti-users feat-icon"></i><div class="feat-text"><strong>Comunidade VIP de Trocas</strong>Grupo exclusivo de membros para trocar, tirar dúvidas e acompanhar tudo sobre figurinhas</div></div>
    </div>
  </div>

  <div class="divider"></div>

  <!-- TESTIMONIALS -->
  <div class="testimonials">
    <div class="section-title">O que os <span class="gold">membros</span> dizem</div>
    <div class="testi-grid">
      <div class="testi">
        <p>"Entrei na comunidade e em menos de uma semana já tinha acesso a todas as Legend. Os PDFs em alta resolução são perfeitos!"</p>
        <strong>— Rafael M., membro VIP</strong>
        <div class="testi-rating">★★★★★</div>
      </div>
      <div class="testi">
        <p>"A plataforma é organizada por seleções, raridades e categorias. Conteúdo exclusivo que não encontro em nenhum outro lugar. Sensacional!"</p>
        <strong>— Priscila S., membro VIP</strong>
        <div class="testi-rating">★★★★★</div>
      </div>
      <div class="testi">
        <p>"As dicas de segurança digital e os bastidores do canal sobre como identificar figurinhas falsas já pagaram o valor sozinhos. Recomendo demais!"</p>
        <strong>— Lucas T., membro desde 2018</strong>
        <div class="testi-rating">★★★★★</div>
      </div>
    </div>
  </div>

  <!-- STEPS -->
  <div class="steps-section">
    <div class="section-title">Como <span class="gold">funciona</span></div>
    <div class="steps-grid">
      <div class="step-card" style="position:relative">
        <div class="step-num">1</div>
        <strong>Entre para a comunidade</strong>
        <p>Clique no botão e garanta seu acesso exclusivo à comunidade VIP da Copa 2026</p>
        <div class="step-arrow"><i class="ti ti-chevron-right"></i></div>
      </div>
      <div class="step-card" style="position:relative">
        <div class="step-num">2</div>
        <strong>Acesso liberado na plataforma</strong>
        <p>Liberado o acesso dentro da plataforma com seu acesso a todos os PDFs</p>
        <div class="step-arrow"><i class="ti ti-chevron-right"></i></div>
      </div>
      <div class="step-card" style="position:relative">
        <div class="step-num">3</div>
        <strong>Receba o acesso VIP imediatamente</strong>
        <p>Receba o acesso VIP imediatamente após a confirmação do pagamento</p>
        <div class="step-arrow"><i class="ti ti-chevron-right"></i></div>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <strong>Aproveite sua coleção de PDF exclusivo</strong>
        <p>Aproveite sua coleção de PDF exclusivo com mais de 1.000 figurinhas e atualizações constantes</p>
      </div>
    </div>
  </div>

  <!-- FOOTER CTA FULLSCREEN -->
  <div class="footer-cta" id="entrar">
    <img src="https://kimi-web-img.moonshot.cn/img/api.aecweb.com.br/4b9e78cc0eaef7e8888d85b638aecfe16b6a8709.webp" alt="Estádio Copa 2026" class="footer-cta-bg">
    <div class="footer-cta-overlay"></div>
    <div class="footer-cta-content">
      <div class="badge-hero" style="margin-bottom:1.5rem"><i class="ti ti-trophy"></i> Oferta por tempo limitado</div>
      <h2>ENTRE NA <span class="gold">COMUNIDADE</span> AGORA</h2>
      <p>Acesso vitalício à comunidade VIP da Copa 2026 — mais de 1.000 figurinhas em PDF, Legend, holográficas e atualizações exclusivas que não encontrará em lugar nenhum.</p><br/>
      <div class="price-tag">
        <s>R$ 49,90</s>
        <strong>R$ 19,90</strong>
        <span>pagamento único — acesso vitalício à comunidade</span>
      </div><br/>
      <a href="https://checkout.perfectpay.com.br/pay/PPU38CQC6TK" class="btn-hero pulse shimmer footer-cta-btn"><i class="ti ti-trophy"></i> ENTRAR AGORA</a>
      <div style="margin-top:1.5rem">
        <a href="mailto:suporte@agenciajob.com" class="btn-sec">Tenho dúvidas</a>
        <a href="https://perfectpay.agenciajob.com/comunidade/login.php" class="btn-sec">Já sou membro — entrar</a>
      </div>
      <div class="seal">
        <i class="ti ti-shield-check"></i> Acesso seguro e garantido · Suporte via Instagram/YouTube · Atualizações constantes
      </div>
    </div>
  </div>

</div>

<footer style="text-align:center;padding:1.5rem;font-size:12px;color:#b8a878;border-top:1px solid #4a4028;background:#12100a">© Perfect Pay · Comunidade exclusiva</footer>

<script>
let end = Date.now() + (23*3600+47*60)*1000;
function tick(){
  let d = Math.max(0, end - Date.now());
  let h = Math.floor(d/3600000);
  let m = Math.floor((d%3600000)/60000);
  let s = Math.floor((d%60000)/1000);
  document.getElementById('t-h').textContent = String(h).padStart(2,'0');
  document.getElementById('t-m').textContent = String(m).padStart(2,'0');
  document.getElementById('t-s').textContent = String(s).padStart(2,'0');
}
tick();
setInterval(tick,1000);

// Smooth scroll
 document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if(target) target.scrollIntoView({behavior:'smooth'});
    });
  });
</script>
<!-- Balão WhatsApp -->
<a href="https://wa.me/5511998475658" 
   class="whatsapp-float" 
   target="_blank">
    
    <svg xmlns="http://www.w3.org/2000/svg" 
         viewBox="0 0 32 32" 
         width="32" 
         height="32" 
         fill="white">
        <path d="M16 .4C7.4.4.4 7.4.4 16c0 2.8.7 5.5 2.1 7.9L0 32l8.3-2.2c2.3 1.3 4.9 1.9 7.7 1.9 8.6 0 15.6-7 15.6-15.6S24.6.4 16 .4zm0 28.3c-2.4 0-4.7-.6-6.7-1.8l-.5-.3-4.9 1.3 1.3-4.8-.3-.5c-1.3-2-1.9-4.3-1.9-6.7 0-7.1 5.8-12.9 12.9-12.9S28.9 8.9 28.9 16 23.1 28.7 16 28.7zm7.1-9.6c-.4-.2-2.2-1.1-2.6-1.2-.3-.1-.6-.2-.9.2-.2.4-1 1.2-1.2 1.4-.2.2-.5.3-.9.1-.4-.2-1.7-.6-3.2-2-1.2-1.1-2-2.5-2.2-2.9-.2-.4 0-.6.2-.8.2-.2.4-.5.6-.7.2-.2.2-.4.3-.6.1-.2 0-.5-.1-.7-.1-.2-.9-2.1-1.2-2.8-.3-.7-.6-.6-.9-.6h-.8c-.3 0-.7.1-1 .5-.3.4-1.3 1.3-1.3 3.1 0 1.8 1.3 3.5 1.5 3.7.2.2 2.5 3.8 6.1 5.3.8.4 1.5.6 2 .7.8.3 1.5.2 2 .1.6-.1 2.2-.9 2.5-1.8.3-.9.3-1.7.2-1.8-.1-.2-.4-.3-.8-.5z"/>
    </svg>
</a>

<style>
.whatsapp-float {
    position: fixed;
    width: 65px;
    height: 65px;
    bottom: 20px;
    right: 20px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    z-index: 9999;
    transition: 0.3s;
    animation: pulse 2s infinite;
}

.whatsapp-float:hover {
    transform: scale(1.1);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}
</style>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-G869ZPLWSB"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-G869ZPLWSB');
</script>
</body>
</html>