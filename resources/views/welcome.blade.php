<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EnvatoPass — Descarga sin límites</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0a0a0f;
    --bg2: #111118;
    --bg3: #18181f;
    --card: #1c1c24;
    --border: rgba(255,255,255,0.07);
    --border2: rgba(255,255,255,0.12);
    --accent: #7c5cfc;
    --accent2: #a87fff;
    --accent-glow: rgba(124,92,252,0.25);
    --green: #22d3a0;
    --green-dim: rgba(34,211,160,0.12);
    --text: #f0eeff;
    --text2: #9896b0;
    --text3: #5e5c78;
    --gold: #f5c842;
    --gold-dim: rgba(245,200,66,0.1);
  }

  html { scroll-behavior: smooth; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    overflow-x: hidden;
    line-height: 1.6;
  }

  /* NOISE OVERLAY */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
    background-size: 180px;
    pointer-events: none;
    z-index: 1000;
    opacity: 0.35;
  }

  /* GLOW BG */
  .glow-orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(120px);
    pointer-events: none;
    z-index: 0;
  }
  .glow-1 { width: 500px; height: 500px; background: rgba(124,92,252,0.12); top: -100px; left: -150px; }
  .glow-2 { width: 400px; height: 400px; background: rgba(34,211,160,0.07); bottom: 200px; right: -100px; }
  .glow-3 { width: 300px; height: 300px; background: rgba(245,200,66,0.05); top: 50%; left: 40%; }

  /* NAV */
  nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border);
    background: rgba(10,10,15,0.8);
    backdrop-filter: blur(20px);
  }

  .logo {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.2rem;
    letter-spacing: -0.02em;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .logo-badge {
    background: linear-gradient(135deg, var(--accent), var(--green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .nav-pill {
    background: var(--card);
    border: 1px solid var(--border2);
    color: var(--text2);
    padding: 0.45rem 1.2rem;
    border-radius: 100px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
  }
  .nav-pill:hover { border-color: var(--accent); color: var(--accent2); }

  .nav-cta {
    background: var(--accent);
    border: none;
    color: white;
    padding: 0.45rem 1.4rem;
    border-radius: 100px;
    font-size: 0.85rem;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
  }
  .nav-cta:hover { background: var(--accent2); transform: translateY(-1px); }

  /* HERO */
  .hero {
    position: relative;
    z-index: 10;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 8rem 1.5rem 4rem;
  }

  .badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--green-dim);
    border: 1px solid rgba(34,211,160,0.25);
    color: var(--green);
    padding: 0.35rem 1rem;
    border-radius: 100px;
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 1.8rem;
    animation: fadeUp 0.6s ease both;
  }

  .pulse-dot {
    width: 6px;
    height: 6px;
    background: var(--green);
    border-radius: 50%;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.4); }
  }

  h1 {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(2.8rem, 7vw, 5.5rem);
    line-height: 1.0;
    letter-spacing: -0.04em;
    max-width: 800px;
    animation: fadeUp 0.6s 0.1s ease both;
  }

  .h1-line2 {
    background: linear-gradient(90deg, var(--accent2), var(--green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .hero-sub {
    font-size: 1.05rem;
    color: var(--text2);
    max-width: 520px;
    margin: 1.5rem auto 2.5rem;
    font-weight: 300;
    line-height: 1.7;
    animation: fadeUp 0.6s 0.2s ease both;
  }

  .hero-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    animation: fadeUp 0.6s 0.3s ease both;
  }

  .btn-primary {
    background: var(--accent);
    color: white;
    border: none;
    padding: 0.85rem 2.2rem;
    border-radius: 100px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
    position: relative;
    overflow: hidden;
  }
  .btn-primary::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,0.1) 0%, transparent 100%);
  }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 40px var(--accent-glow); }

  .btn-ghost {
    background: transparent;
    color: var(--text2);
    border: 1px solid var(--border2);
    padding: 0.85rem 2rem;
    border-radius: 100px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
    text-decoration: none;
  }
  .btn-ghost:hover { border-color: var(--accent); color: var(--accent2); }

  .hero-stats {
    margin-top: 4rem;
    display: flex;
    gap: 3rem;
    justify-content: center;
    flex-wrap: wrap;
    animation: fadeUp 0.6s 0.4s ease both;
  }

  .stat {
    text-align: center;
  }
  .stat-num {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 1.8rem;
    color: var(--text);
    letter-spacing: -0.03em;
  }
  .stat-label {
    font-size: 0.78rem;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-top: 2px;
  }

  /* MARQUEE */
  .marquee-section {
    position: relative;
    z-index: 10;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    padding: 1rem 0;
    overflow: hidden;
    background: var(--bg2);
  }
  .marquee-track {
    display: flex;
    gap: 2rem;
    animation: marquee 22s linear infinite;
    width: max-content;
  }
  .marquee-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    color: var(--text3);
    white-space: nowrap;
    padding: 0 1rem;
  }
  .marquee-dot { width: 4px; height: 4px; background: var(--accent); border-radius: 50%; }
  @keyframes marquee {
    from { transform: translateX(0); }
    to { transform: translateX(-50%); }
  }

  /* HOW IT WORKS */
  section {
    position: relative;
    z-index: 10;
  }

  .section-pad {
    padding: 6rem 1.5rem;
    max-width: 1100px;
    margin: 0 auto;
  }

  .label-tag {
    display: inline-block;
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--accent2);
    font-weight: 500;
    margin-bottom: 0.8rem;
  }

  .section-title {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    letter-spacing: -0.03em;
    line-height: 1.1;
    margin-bottom: 1rem;
  }

  .section-sub {
    font-size: 1rem;
    color: var(--text2);
    max-width: 480px;
    font-weight: 300;
  }

  /* STEPS */
  .steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5px;
    margin-top: 3.5rem;
    border: 1.5px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
  }

  .step-card {
    background: var(--card);
    padding: 2.2rem;
    position: relative;
    transition: background 0.2s;
  }
  .step-card:hover { background: #20202a; }

  .step-num {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 3.5rem;
    color: var(--border2);
    line-height: 1;
    margin-bottom: 1rem;
    letter-spacing: -0.04em;
  }

  .step-icon {
    width: 44px;
    height: 44px;
    background: var(--accent-glow);
    border: 1px solid rgba(124,92,252,0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 1.2rem;
  }

  .step-title {
    font-family: 'Syne', sans-serif;
    font-weight: 600;
    font-size: 1.05rem;
    margin-bottom: 0.6rem;
    color: var(--text);
  }

  .step-desc {
    font-size: 0.88rem;
    color: var(--text2);
    line-height: 1.6;
    font-weight: 300;
  }

  /* PRICING */
  .pricing-section {
    background: var(--bg2);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
  }

  .pricing-card {
    max-width: 460px;
    margin: 3.5rem auto 0;
    background: var(--card);
    border: 1px solid var(--border2);
    border-radius: 24px;
    overflow: hidden;
    position: relative;
  }

  .pricing-glow {
    position: absolute;
    top: -80px;
    left: 50%;
    transform: translateX(-50%);
    width: 300px;
    height: 200px;
    background: var(--accent-glow);
    filter: blur(60px);
    pointer-events: none;
  }

  .pricing-header {
    padding: 2.5rem 2.5rem 2rem;
    border-bottom: 1px solid var(--border);
    text-align: center;
    position: relative;
  }

  .popular-tag {
    display: inline-block;
    background: linear-gradient(135deg, var(--accent), var(--green));
    color: white;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.3rem 1rem;
    border-radius: 100px;
    margin-bottom: 1.5rem;
  }

  .price-amount {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 4px;
    margin-bottom: 0.4rem;
  }

  .price-currency {
    font-family: 'Syne', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--text2);
    padding-top: 0.6rem;
  }

  .price-num {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 4.5rem;
    line-height: 1;
    letter-spacing: -0.04em;
    color: var(--text);
  }

  .price-period {
    font-size: 0.82rem;
    color: var(--text3);
    text-align: center;
    margin-bottom: 0.2rem;
  }

  .price-note {
    font-size: 0.78rem;
    color: var(--green);
    font-weight: 500;
  }

  .pricing-body {
    padding: 2rem 2.5rem 2.5rem;
  }

  .feature-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    margin-bottom: 2rem;
  }

  .feature-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.9rem;
    color: var(--text2);
  }

  .check-icon {
    width: 18px;
    height: 18px;
    background: var(--green-dim);
    border-radius: 50%;
    border: 1px solid rgba(34,211,160,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
    color: var(--green);
    font-size: 0.6rem;
  }

  .feature-strong {
    color: var(--text);
    font-weight: 500;
  }

  .btn-full {
    width: 100%;
    background: var(--accent);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'DM Sans', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
    position: relative;
    overflow: hidden;
  }
  .btn-full::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, transparent 100%);
  }
  .btn-full:hover { transform: translateY(-2px); box-shadow: 0 16px 50px var(--accent-glow); }

  .secure-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 1rem;
    font-size: 0.78rem;
    color: var(--text3);
  }

  /* INPUT SECTION */
  .input-section {
    padding: 5rem 1.5rem;
    max-width: 700px;
    margin: 0 auto;
    text-align: center;
  }

  .input-wrapper {
    margin-top: 2.5rem;
    background: var(--card);
    border: 1px solid var(--border2);
    border-radius: 16px;
    padding: 1.5rem;
    text-align: left;
  }

  .input-label {
    font-size: 0.8rem;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.6rem;
    display: block;
  }

  .input-row {
    display: flex;
    gap: 10px;
  }

  .url-input {
    flex: 1;
    background: var(--bg);
    border: 1px solid var(--border2);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.2s;
  }
  .url-input::placeholder { color: var(--text3); }
  .url-input:focus { border-color: var(--accent); }

  .download-btn {
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.4rem;
    font-weight: 500;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    transition: all 0.2s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .download-btn:hover { background: var(--accent2); }

  .input-hint {
    margin-top: 0.7rem;
    font-size: 0.78rem;
    color: var(--text3);
  }

  /* SOCIAL PROOF */
  .reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-top: 3rem;
  }

  .review-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    transition: border-color 0.2s;
  }
  .review-card:hover { border-color: var(--border2); }

  .stars {
    color: var(--gold);
    font-size: 0.8rem;
    letter-spacing: 2px;
    margin-bottom: 0.8rem;
  }

  .review-text {
    font-size: 0.88rem;
    color: var(--text2);
    line-height: 1.6;
    margin-bottom: 1rem;
    font-weight: 300;
    font-style: italic;
  }

  .reviewer {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.75rem;
    flex-shrink: 0;
  }
  .av1 { background: rgba(124,92,252,0.2); color: var(--accent2); border: 1px solid rgba(124,92,252,0.3); }
  .av2 { background: var(--green-dim); color: var(--green); border: 1px solid rgba(34,211,160,0.25); }
  .av3 { background: var(--gold-dim); color: var(--gold); border: 1px solid rgba(245,200,66,0.25); }

  .reviewer-name {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text);
  }

  .reviewer-role {
    font-size: 0.75rem;
    color: var(--text3);
  }

  /* FAQ */
  .faq-list {
    margin-top: 3rem;
    display: flex;
    flex-direction: column;
    gap: 0;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
  }

  .faq-item {
    border-bottom: 1px solid var(--border);
    overflow: hidden;
  }
  .faq-item:last-child { border-bottom: none; }

  .faq-q {
    width: 100%;
    background: var(--card);
    border: none;
    color: var(--text);
    padding: 1.3rem 1.5rem;
    text-align: left;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    transition: background 0.15s;
  }
  .faq-q:hover { background: #20202a; }

  .faq-arrow {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.2s, background 0.2s;
    font-size: 0.7rem;
    color: var(--text3);
  }

  .faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
  }
  .faq-answer.open { max-height: 200px; }

  .faq-answer-inner {
    padding: 0 1.5rem 1.3rem;
    font-size: 0.88rem;
    color: var(--text2);
    line-height: 1.7;
    font-weight: 300;
    background: var(--card);
  }

  .faq-q.active .faq-arrow { transform: rotate(180deg); background: var(--accent-glow); color: var(--accent2); }

  /* FOOTER */
  footer {
    position: relative;
    z-index: 10;
    border-top: 1px solid var(--border);
    padding: 2.5rem 1.5rem;
    text-align: center;
  }

  .footer-logo {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.1rem;
    letter-spacing: -0.02em;
    margin-bottom: 0.6rem;
  }

  .footer-note {
    font-size: 0.78rem;
    color: var(--text3);
    max-width: 400px;
    margin: 0 auto;
    line-height: 1.6;
  }

  /* ANIMATIONS */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s ease;
  }
  .reveal.visible {
    opacity: 1;
    transform: translateY(0);
  }

  /* RESPONSIVE */
  @media (max-width: 640px) {
    nav { padding: 1rem; }
    .nav-ghost { display: none; }
    .hero { padding: 7rem 1rem 3rem; }
    .hero-stats { gap: 2rem; }
    .input-row { flex-direction: column; }
    .pricing-header, .pricing-body { padding: 1.5rem; }
  }
</style>
</head>
<body>

<div class="glow-orb glow-1"></div>
<div class="glow-orb glow-2"></div>
<div class="glow-orb glow-3"></div>

<!-- NAV -->
<nav>
  <div class="logo">
    <span>⬡</span>
    <span>Envato<span class="logo-badge">Pass</span></span>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <a href="#como-funciona" class="nav-pill nav-ghost">¿Cómo funciona?</a>
    <a href="#precio" class="nav-cta">Obtener acceso</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="badge-pill">
    <span class="pulse-dot"></span>
    Acceso disponible ahora mismo
  </div>

  <h1>
    Descarga todo de<br>
    <span class="h1-line2">Envato Elements</span><br>
    por solo S/20
  </h1>

  <p class="hero-sub">
    Solo pega el enlace del recurso que quieres. Nosotros lo descargamos por ti en segundos. Sin suscripciones complicadas.
  </p>

  <div class="hero-actions">
    <a href="#precio" class="btn-primary">
      Empezar por S/20
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    <a href="#como-funciona" class="btn-ghost">Ver cómo funciona</a>
  </div>

  <div class="hero-stats">
    <div class="stat">
      <div class="stat-num">+500</div>
      <div class="stat-label">Clientes activos</div>
    </div>
    <div class="stat" style="border-left:1px solid var(--border);border-right:1px solid var(--border);padding:0 3rem;">
      <div class="stat-num">+50K</div>
      <div class="stat-label">Archivos entregados</div>
    </div>
    <div class="stat">
      <div class="stat-num">~30s</div>
      <div class="stat-label">Tiempo de entrega</div>
    </div>
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-section">
  <div class="marquee-track">
    <div class="marquee-item"><span class="marquee-dot"></span> Plantillas Premiere Pro</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Mockups PSD</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Fuentes Premium</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Ilustraciones Vectoriales</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Efectos de Sonido</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Templates After Effects</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Plugins WordPress</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Stock Photos</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Plantillas Premiere Pro</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Mockups PSD</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Fuentes Premium</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Ilustraciones Vectoriales</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Efectos de Sonido</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Templates After Effects</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Plugins WordPress</div>
    <div class="marquee-item"><span class="marquee-dot"></span> Stock Photos</div>
  </div>
</div>

<!-- CÓMO FUNCIONA -->
<section id="como-funciona">
  <div class="section-pad">
    <div class="reveal">
      <div class="label-tag">Proceso</div>
      <h2 class="section-title">Tan simple como<br>pegar un enlace</h2>
      <p class="section-sub">Sin cuentas complicadas. Sin esperas. Funciona en 3 pasos.</p>
    </div>

    <div class="steps-grid reveal">
      <div class="step-card">
        <div class="step-num">01</div>
        <div class="step-icon">Pago</div>
        <div class="step-title">Compra tu acceso</div>
        <div class="step-desc">Elige tu plan (1 mes, 6 meses o 1 año). Incluye 1 día gratis para probar y recibes acceso inmediato a nuestra plataforma.</div>
      </div>
      <div class="step-card">
        <div class="step-num">02</div>
        <div class="step-icon">Enlace</div>
        <div class="step-title">Pega el enlace</div>
        <div class="step-desc">Copia la URL del recurso en Envato Elements y pégala en nuestra plataforma.</div>
      </div>
      <div class="step-card">
        <div class="step-num">03</div>
        <div class="step-icon">Descarga</div>
        <div class="step-title">Descarga al instante</div>
        <div class="step-desc">El archivo aparece listo para descargar en segundos, directo a tu dispositivo.</div>
      </div>
    </div>

  </div>
</section>

<!-- PRICING -->
<section id="precio" class="pricing-section">
  <div class="section-pad" style="text-align:center;">
    <div class="reveal">
      <div class="label-tag">Planes con 1 día gratis</div>
      <h2 class="section-title">Elige tu plan.<br>Empieza hoy.</h2>
      <p class="section-sub" style="margin:0 auto;">1 mes por S/20, 6 meses por S/100 o 1 año por S/180. Incluye 1 día gratis para probar.</p>
    </div>

    <div class="pricing-cards-wrap" style="display:flex; flex-wrap:wrap; gap:1.5rem; justify-content:center; margin-top:2rem;">
      <!-- Plan 1 mes -->
      <div class="pricing-card reveal" style="margin:0; max-width:360px;">
        <div class="pricing-glow"></div>
        <div class="pricing-header">
          <div class="popular-tag">Plan 1 mes</div>
          <div class="price-amount" style="flex-direction: column; gap: 0.55rem; margin-top: 0.25rem;">
            <div style="font-size:1.2rem; font-weight:800; color: var(--text);">S/20</div>
            <div style="font-size:0.92rem; font-weight:600; color: var(--text2);">Incluye 1 día gratis</div>
          </div>
          <div class="price-period">Acceso por 1 mes · descarga al instante</div>
          <div class="price-note" style="margin-top:0.3rem;">Paga con Yape, Plin o transferencia</div>
        </div>
        <div class="pricing-body">
          <ul class="feature-list">
            <li class="feature-item"><span class="check-icon">✓</span><span>Descarga <span class="feature-strong">recursos ilimitados</span> de Envato Elements</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span><span class="feature-strong">Solo pega el enlace</span> — nosotros hacemos el resto</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Entrega en <span class="feature-strong">menos de 30 segundos</span></span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Acceso <span class="feature-strong">24/7</span> sin interrupciones</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Soporte por <span class="feature-strong">WhatsApp</span> incluido</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Acceso por el periodo seleccionado (1 mes)</span></li>
          </ul>
          <a href="#comprar" class="btn-full">
            Elegir 1 mes
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <div class="secure-note">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Pago seguro · Acceso inmediato
          </div>
        </div>
      </div>

      <!-- Plan 6 meses -->
      <div class="pricing-card reveal" style="margin:0; max-width:360px;">
        <div class="pricing-glow"></div>
        <div class="pricing-header">
          <div class="popular-tag">Plan 6 meses</div>
          <div class="price-amount" style="flex-direction: column; gap: 0.55rem; margin-top: 0.25rem;">
            <div style="font-size:1.2rem; font-weight:800; color: var(--text);">S/100</div>
            <div style="font-size:0.92rem; font-weight:600; color: var(--text2);">Incluye 1 día gratis</div>
          </div>
          <div class="price-period">Acceso por 6 meses · descarga al instante</div>
          <div class="price-note" style="margin-top:0.3rem;">Paga con Yape, Plin o transferencia</div>
        </div>
        <div class="pricing-body">
          <ul class="feature-list">
            <li class="feature-item"><span class="check-icon">✓</span><span>Descarga <span class="feature-strong">recursos ilimitados</span> de Envato Elements</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span><span class="feature-strong">Solo pega el enlace</span> — nosotros hacemos el resto</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Entrega en <span class="feature-strong">menos de 30 segundos</span></span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Acceso <span class="feature-strong">24/7</span> sin interrupciones</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Soporte por <span class="feature-strong">WhatsApp</span> incluido</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Acceso por el periodo seleccionado (6 meses)</span></li>
          </ul>
          <a href="#comprar" class="btn-full">
            Elegir 6 meses
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <div class="secure-note">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Pago seguro · Acceso inmediato
          </div>
        </div>
      </div>

      <!-- Plan 1 año -->
      <div class="pricing-card reveal" style="margin:0; max-width:360px;">
        <div class="pricing-glow"></div>
        <div class="pricing-header">
          <div class="popular-tag">Plan 1 año</div>
          <div class="price-amount" style="flex-direction: column; gap: 0.55rem; margin-top: 0.25rem;">
            <div style="font-size:1.2rem; font-weight:800; color: var(--text);">S/180</div>
            <div style="font-size:0.92rem; font-weight:600; color: var(--text2);">Incluye 1 día gratis</div>
          </div>
          <div class="price-period">Acceso por 1 año · descarga al instante</div>
          <div class="price-note" style="margin-top:0.3rem;">Paga con Yape, Plin o transferencia</div>
        </div>
        <div class="pricing-body">
          <ul class="feature-list">
            <li class="feature-item"><span class="check-icon">✓</span><span>Descarga <span class="feature-strong">recursos ilimitados</span> de Envato Elements</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span><span class="feature-strong">Solo pega el enlace</span> — nosotros hacemos el resto</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Entrega en <span class="feature-strong">menos de 30 segundos</span></span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Acceso <span class="feature-strong">24/7</span> sin interrupciones</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Soporte por <span class="feature-strong">WhatsApp</span> incluido</span></li>
            <li class="feature-item"><span class="check-icon">✓</span><span>Acceso por el periodo seleccionado (1 año)</span></li>
          </ul>
          <a href="#comprar" class="btn-full">
            Elegir 1 año
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
          <div class="secure-note">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Pago seguro · Acceso inmediato
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- REVIEWS -->
<section>
  <div class="section-pad">
    <div class="reveal" style="text-align:center;margin-bottom:0;">
      <div class="label-tag">Testimonios</div>
      <h2 class="section-title">Lo que dicen nuestros<br>clientes</h2>
    </div>
    <div class="reviews-grid reveal">
      <div class="review-card">
        <div class="stars">★★★★★</div>
        <p class="review-text">"Increíble servicio. Pegué el enlace y en 20 segundos ya tenía el template de Premiere listo. Totalmente recomendado."</p>
        <div class="reviewer">
          <div class="avatar av1">CR</div>
          <div>
            <div class="reviewer-name">Carlos R.</div>
            <div class="reviewer-role">Editor de video · Lima</div>
          </div>
        </div>
      </div>
      <div class="review-card">
        <div class="stars">★★★★★</div>
        <p class="review-text">"Ahorré muchísimo. Antes pagaba la suscripción completa para un par de recursos. Aquí con S/20 tengo acceso a todo."</p>
        <div class="reviewer">
          <div class="avatar av2">MP</div>
          <div>
            <div class="reviewer-name">María P.</div>
            <div class="reviewer-role">Diseñadora gráfica · Arequipa</div>
          </div>
        </div>
      </div>
      <div class="review-card">
        <div class="stars">★★★★★</div>
        <p class="review-text">"Súper fácil y rápido. El soporte por WhatsApp respondió al instante cuando tuve una duda. 10/10."</p>
        <div class="reviewer">
          <div class="avatar av3">JL</div>
          <div>
            <div class="reviewer-name">Jesús L.</div>
            <div class="reviewer-role">Motion designer · Trujillo</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section style="background:var(--bg2);border-top:1px solid var(--border);">
  <div class="section-pad" style="max-width:680px;">
    <div class="reveal" style="text-align:center;margin-bottom:0;">
      <div class="label-tag">FAQ</div>
      <h2 class="section-title">Preguntas frecuentes</h2>
    </div>

    <div class="faq-list reveal">
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">
          ¿Qué puedo descargar exactamente?
          <span class="faq-arrow">▾</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Puedes descargar cualquier recurso disponible en Envato Elements: plantillas de video, motion graphics, música, efectos de sonido, fuentes, mockups, ilustraciones, fotos y más.</div>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">
          ¿Cómo pago?
          <span class="faq-arrow">▾</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Aceptamos Yape, Plin, transferencia bancaria y otros métodos locales. Una vez confirmado el pago, recibes tu acceso inmediatamente por WhatsApp.</div>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">
          ¿Hay límite de descargas?
          <span class="faq-arrow">▾</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">No hay un límite estricto de descargas. El acceso es amplio y pensado para uso personal o profesional. Para uso masivo o empresarial, contáctanos.</div>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">
          ¿El acceso caduca?
          <span class="faq-arrow">▾</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">El acceso es de larga duración. Pagás una sola vez y usas el servicio sin preocuparte por renovaciones mensuales ni cargos automáticos.</div>
        </div>
      </div>
      <div class="faq-item" style="border-bottom:none;">
        <button class="faq-q" onclick="toggleFaq(this)">
          ¿Tienen soporte si tengo problemas?
          <span class="faq-arrow">▾</span>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Sí, contamos con soporte por WhatsApp disponible todos los días. Respondemos en minutos para ayudarte a completar tus descargas.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA FINAL -->
<section id="comprar" style="border-top:1px solid var(--border);">
  <div class="section-pad" style="text-align:center;padding:5rem 1.5rem;">
    <div class="reveal">
      <h2 class="section-title">¿Listo para empezar?</h2>
      <p class="section-sub" style="margin:1rem auto 2.5rem;">Únete a cientos de diseñadores y creadores que ya descargan sin límites.</p>
      <a href="https://wa.me/51917080235?text=Hola,%20quiero%20obtener%20acceso%20a%20EnvatoPass%20por%20S/20" target="_blank" rel="noopener noreferrer" class="btn-primary" style="margin:0 auto;display:inline-flex;">
        Obtener acceso ahora — S/20
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <div class="secure-note" style="justify-content:center;margin-top:1.2rem;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Pago seguro · Acceso inmediato · Soporte por WhatsApp
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-logo">Envato<span class="logo-badge">Pass</span></div>
  <p class="footer-note" style="margin-top:0.5rem;">Servicio independiente de descarga de recursos digitales. No somos afiliados de Envato Pty Ltd.</p>
  <p class="footer-note" style="margin-top:0.8rem;">© 2026 EnvatoPass · Lima, Perú</p>
</footer>

<script>
function toggleFaq(btn) {
  const answer = btn.nextElementSibling;
  const isOpen = answer.classList.contains('open');
  document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('open'));
  document.querySelectorAll('.faq-q').forEach(b => b.classList.remove('active'));
  if (!isOpen) {
    answer.classList.add('open');
    btn.classList.add('active');
  }
}

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>