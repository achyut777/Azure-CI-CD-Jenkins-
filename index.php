<?php
$greeting = "Hello, World!";
$subtitle = "Welcome to my PHP page";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Hello World</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --bg:       #0b0c10;
      --surface:  #13141a;
      --accent:   #e8c46a;
      --glow:     rgba(232, 196, 106, 0.18);
      --text:     #f0ece0;
      --muted:    #6b6860;
    }

    body {
      background-color: var(--bg);
      color: var(--text);
      font-family: 'DM Mono', monospace;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    /* Noise overlay */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
    }

    /* Radial glow */
    body::after {
      content: '';
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, var(--glow) 0%, transparent 70%);
      pointer-events: none;
      z-index: 0;
    }

    .card {
      position: relative;
      z-index: 1;
      background: var(--surface);
      border: 1px solid rgba(232, 196, 106, 0.15);
      border-radius: 4px;
      padding: 60px 72px;
      text-align: center;
      max-width: 580px;
      width: 90%;
      box-shadow: 0 0 60px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.04);
      animation: rise 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes rise {
      from { opacity: 0; transform: translateY(32px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0)   scale(1);    }
    }

    .tag {
      font-size: 0.65rem;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 28px;
      display: block;
      animation: fade 1s 0.3s ease both;
    }

    .heading {
      font-family: 'Playfair Display', serif;
      font-size: clamp(2.6rem, 6vw, 4rem);
      font-weight: 900;
      line-height: 1.05;
      letter-spacing: -0.01em;
      color: var(--text);
      animation: fade 1s 0.5s ease both;
    }

    .heading span {
      color: var(--accent);
      position: relative;
      display: inline-block;
    }

    /* Underline accent */
    .heading span::after {
      content: '';
      position: absolute;
      left: 0; bottom: -4px;
      width: 100%; height: 2px;
      background: var(--accent);
      transform: scaleX(0);
      transform-origin: left;
      animation: line 0.6s 1.1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes line {
      to { transform: scaleX(1); }
    }

    .divider {
      margin: 28px auto;
      width: 48px;
      height: 1px;
      background: var(--accent);
      opacity: 0.35;
      animation: fade 1s 0.65s ease both;
    }

    .subtitle {
      font-size: 0.78rem;
      letter-spacing: 0.12em;
      color: var(--muted);
      animation: fade 1s 0.75s ease both;
    }

    .badge {
      display: inline-block;
      margin-top: 36px;
      padding: 6px 16px;
      border: 1px solid rgba(232, 196, 106, 0.3);
      border-radius: 2px;
      font-size: 0.68rem;
      letter-spacing: 0.18em;
      color: var(--accent);
      text-transform: uppercase;
      animation: fade 1s 0.9s ease both;
    }

    @keyframes fade {
      from { opacity: 0; }
      to   { opacity: 1; }
    }
  </style>
</head>
<body>

  <div class="card">
    <span class="tag">PHP &bull; <?php echo date('Y'); ?></span>
    <h1 class="heading">
      <?php
        // Split "Hello, World!" into two parts for styling
        $parts = explode(',', $greeting, 2);
        echo htmlspecialchars($parts[0]) . ', <span>' . ltrim(htmlspecialchars($parts[1])) . '</span>';
      ?>
    </h1>
    <div class="divider"></div>
    <p class="subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
    <div class="badge">Rendered by PHP</div>
  </div>

</body>
</html>
