<?php
// index.php — Galaxy Luxury Kitchenware (single-page, SEO-friendly, images + PHP contact form)

// -------------------------
// Site configuration
// -------------------------
$siteName = "Galaxy Luxury Kitchenware";
$tagline  = "Imported luxury kitchenware supplier in Lilburn, GA";
$address  = "4694 Arrowhead Trail SW, Lilburn, GA 30047, USA";
$phone    = "4044840250";
$emailTo  = "galaxyluxurykitchen@mail.com";

// IMPORTANT for SEO: replace with your real domain once hosted
$siteUrl  = "https://www.example.com"; // CHANGE THIS
$canonicalUrl = rtrim($siteUrl, "/") . "/";

// Brand visuals (paths relative to this index.php)
$images = [
  "hero"     => "hero-luxury-kitchen.jpg",  // 21:9
  "about"    => "about-showroom.jpg",       // 4:3
  "svc1"     => "services-collection.jpg",  // 3:2
  "svc2"     => "services-sourcing.jpg",    // 3:2
  "feature1" => "feature-artisan.jpg",      // 1:1
  "feature2" => "feature-tableware.jpg",    // 1:1
  "feature3" => "feature-cookware.jpg",     // 1:1
  "tall"     => "feature-tall.jpg",         // 4:5
  "contact"  => "contact-banner.jpg",       // 16:9
];

// For better deliverability: ideally use an address on your domain (e.g. no-reply@yourdomain.com)
// Many hosts reject mail() if From doesn't match your domain.
$fromEmail = $emailTo;

// -------------------------
// Helpers
// -------------------------
function h($v) { return htmlspecialchars($v ?? "", ENT_QUOTES, "UTF-8"); }

// -------------------------
// Contact form handling
// -------------------------
$form = ["name"=>"","email"=>"","phone"=>"","subject"=>"","message"=>""];
$errors = [];
$success = false;

session_start();
if (!isset($_SESSION["csrf_token"]) || !is_string($_SESSION["csrf_token"]) || strlen($_SESSION["csrf_token"]) < 20) {
  $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION["csrf_token"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["contact_form"])) {
  // CSRF
  $postedToken  = $_POST["csrf_token"] ?? "";
  if (!$postedToken || !hash_equals($csrfToken, $postedToken)) {
    $errors[] = "Security check failed. Please refresh and try again.";
  }

  // Honeypot (bots fill hidden inputs)
  $honeypot = trim($_POST["company_website"] ?? "");
  if ($honeypot !== "") {
    $errors[] = "Submission rejected.";
  }

  // Collect
  $form["name"]    = trim($_POST["name"] ?? "");
  $form["email"]   = trim($_POST["email"] ?? "");
  $form["phone"]   = trim($_POST["phone"] ?? "");
  $form["subject"] = trim($_POST["subject"] ?? "");
  $form["message"] = trim($_POST["message"] ?? "");

  // Validate
  if ($form["name"] === "" || mb_strlen($form["name"]) < 2) $errors[] = "Name is required (min 2 characters).";
  if ($form["email"] === "" || !filter_var($form["email"], FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
  if ($form["subject"] === "" || mb_strlen($form["subject"]) < 3) $errors[] = "Subject is required (min 3 characters).";
  if ($form["message"] === "" || mb_strlen($form["message"]) < 10) $errors[] = "Message is required (min 10 characters).";
  if ($form["phone"] !== "" && !preg_match('/^[0-9+\-\s().]{7,25}$/', $form["phone"])) $errors[] = "Phone format looks invalid.";

  if (!$errors) {
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $ua = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";
    $subjectSafe = preg_replace("/[\r\n]+/", " ", $form["subject"]); // prevent header injection

    $body =
      "New website inquiry\n\n" .
      "Name: {$form["name"]}\n" .
      "Email: {$form["email"]}\n" .
      "Phone: " . ($form["phone"] ?: "-") . "\n" .
      "Subject: {$subjectSafe}\n\n" .
      "Message:\n{$form["message"]}\n\n" .
      "----\nIP: {$ip}\nUser-Agent: {$ua}\n";

    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "From: {$siteName} <{$fromEmail}>";
    $headers[] = "Reply-To: {$form["name"]} <{$form["email"]}>";

    $sent = @mail($emailTo, "[Website] {$subjectSafe}", $body, implode("\r\n", $headers));

    if ($sent) {
      $success = true;
      $form = ["name"=>"","email"=>"","phone"=>"","subject"=>"","message"=>""];
    } else {
      // Fallback logging so you don't lose leads if mail() isn't configured
      $logLine = "[" . date("Y-m-d H:i:s") . "] " . str_replace("\n", " | ", $body) . "\n\n";
      @file_put_contents(__DIR__ . "/contact_submissions.log", $logLine, FILE_APPEND);
      $errors[] = "Message saved, but email delivery is not configured on this server. Please call or email directly.";
    }
  }
}

// -------------------------
// SEO meta
// -------------------------
$pageTitle = "{$siteName} | Imported Luxury High-End Kitchenware Supplier";
$description = "Galaxy Luxury Kitchenware supplies imported, luxury, high-end kitchenware: premium cookware, tableware, cutlery, and curated collections for discerning homes, designers, and hospitality.";
$keywords = "luxury kitchenware, imported kitchenware, high-end cookware, premium tableware, luxury cutlery, kitchenware supplier, Lilburn GA kitchenware";
$ogImage = rtrim($siteUrl, "/") . "/" . $images["hero"]; // best effort
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />

  <title><?php echo h($pageTitle); ?></title>
  <meta name="description" content="<?php echo h($description); ?>" />
  <meta name="keywords" content="<?php echo h($keywords); ?>" />
  <meta name="author" content="<?php echo h($siteName); ?>" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
  <link rel="canonical" href="<?php echo h($canonicalUrl); ?>" />

  <!-- Optional: if you have these files, upload them. If not, remove these lines -->
  <link rel="icon" href="favicon.ico" />
  <link rel="apple-touch-icon" href="apple-touch-icon.png" />
  <meta name="theme-color" content="#0b0b0c" />

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?php echo h($siteName); ?>" />
  <meta property="og:title" content="<?php echo h($pageTitle); ?>" />
  <meta property="og:description" content="<?php echo h($description); ?>" />
  <meta property="og:url" content="<?php echo h($canonicalUrl); ?>" />
  <meta property="og:image" content="<?php echo h($ogImage); ?>" />
  <meta property="og:image:alt" content="Luxury kitchenware collection from <?php echo h($siteName); ?>" />

  <!-- Twitter -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?php echo h($pageTitle); ?>" />
  <meta name="twitter:description" content="<?php echo h($description); ?>" />
  <meta name="twitter:image" content="<?php echo h($ogImage); ?>" />

  <!-- LocalBusiness schema (SEO) -->
  <script type="application/ld+json">
  <?php
    $schema = [
      "@context" => "https://schema.org",
      "@type" => "LocalBusiness",
      "name" => $siteName,
      "description" => $description,
      "telephone" => $phone,
      "email" => $emailTo,
      "url" => $canonicalUrl,
      "image" => $ogImage,
      "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "4694 Arrowhead Trail SW",
        "addressLocality" => "Lilburn",
        "addressRegion" => "GA",
        "postalCode" => "30047",
        "addressCountry" => "US"
      ],
      "areaServed" => "US",
      "makesOffer" => [
        ["@type"=>"Offer","name"=>"Imported Cookware Collections"],
        ["@type"=>"Offer","name"=>"Luxury Tableware & Dining Sets"],
        ["@type"=>"Offer","name"=>"Sourcing for Designers & Hospitality"]
      ]
    ];
    echo json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  ?>
  </script>

  <style>
    :root{
      --bg:#070708;
      --panel:#0e0e10;
      --panel2:#121215;
      --text:#f5f2ea;
      --muted:#b7b1a3;
      --line:rgba(245,242,234,.12);
      --gold:#d6b46c;
      --gold2:#b18a3a;
      --shadow:0 18px 45px rgba(0,0,0,.55);
      --radius:18px;
      --max:1120px;
      --font: ui-serif, Georgia, "Times New Roman", Times, serif;
      --sans: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;
    }

    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{
      margin:0;
      color:var(--text);
      background:
        radial-gradient(1200px 600px at 70% -10%, rgba(214,180,108,.12), transparent 55%),
        radial-gradient(900px 500px at 10% 10%, rgba(214,180,108,.08), transparent 55%),
        var(--bg);
      line-height:1.6;
    }
    a{color:inherit;text-decoration:none}
    img{max-width:100%; display:block}

    .container{max-width:var(--max); margin:0 auto; padding:0 22px}
    .skip{position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden}
    .skip:focus{
      left:22px; top:18px; width:auto; height:auto; padding:10px 12px;
      background:var(--panel); border:1px solid var(--line); border-radius:12px; z-index:9999;
    }

    /* Header */
    header{
      position:sticky; top:0; z-index:999;
      backdrop-filter: blur(10px);
      background: rgba(7,7,8,.72);
      border-bottom:1px solid var(--line);
    }
    .topbar{
      display:flex; align-items:center; justify-content:space-between;
      gap:14px; padding:14px 0;
    }
    .brand{
      display:flex; flex-direction:column; gap:2px;
      min-width: 220px;
    }
    .brand strong{
      font-family:var(--font);
      font-weight:700;
      letter-spacing:.2px;
      font-size:18px;
    }
    .brand span{
      font-family:var(--sans);
      color:var(--muted);
      font-size:12px;
      letter-spacing:.12em;
      text-transform:uppercase;
    }

    nav ul{list-style:none; margin:0; padding:0; display:flex; gap:10px; align-items:center}
    nav a{
      font-family:var(--sans);
      font-size:13px;
      color:var(--muted);
      padding:10px 12px;
      border-radius:999px;
      border:1px solid transparent;
      transition: all .15s ease;
    }
    nav a:hover{
      color:var(--text);
      border-color:rgba(214,180,108,.25);
      background:rgba(214,180,108,.06);
    }
    nav a.active{
      color:var(--text);
      border-color:rgba(214,180,108,.40);
      background:rgba(214,180,108,.10);
    }

    .btn{
      font-family:var(--sans);
      display:inline-flex; align-items:center; justify-content:center;
      padding:10px 14px;
      border-radius:999px;
      border:1px solid rgba(214,180,108,.45);
      background: linear-gradient(180deg, rgba(214,180,108,.18), rgba(214,180,108,.08));
      color:var(--text);
      font-size:13px;
      letter-spacing:.02em;
      cursor:pointer;
      transition: all .15s ease;
      white-space:nowrap;
    }
    .btn:hover{background: linear-gradient(180deg, rgba(214,180,108,.24), rgba(214,180,108,.10))}
    .menuBtn{display:none}

    /* Mobile nav */
    .mobileNav{
      display:none;
      border-top:1px solid var(--line);
      padding:10px 0 14px;
    }
    .mobileNav a{
      display:block;
      padding:10px 12px;
      border-radius:12px;
      font-family:var(--sans);
      color:var(--muted);
      border:1px solid transparent;
    }
    .mobileNav a:hover{
      color:var(--text);
      border-color:rgba(214,180,108,.25);
      background:rgba(214,180,108,.06);
    }
    .mobileNav a.active{
      color:var(--text);
      border-color:rgba(214,180,108,.40);
      background:rgba(214,180,108,.10);
    }

    /* Sections */
    section{padding:72px 0}
    .sectionTitle{
      display:flex; align-items:flex-end; justify-content:space-between;
      gap:16px; margin-bottom:18px;
    }
    .sectionTitle h2{
      margin:0;
      font-family:var(--font);
      font-size:28px;
      letter-spacing:-.01em;
    }
    .sectionTitle p{
      margin:0;
      font-family:var(--sans);
      color:var(--muted);
      font-size:14px;
      max-width:520px;
    }

    /* Cards / panels */
    .panel{
      background: linear-gradient(180deg, rgba(18,18,21,.86), rgba(14,14,16,.86));
      border:1px solid var(--line);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .pad{padding:18px}

    /* Hero */
    .hero{
      padding:0;
    }
    .heroMedia{
      position:relative;
      min-height: 72vh;
      display:flex;
      align-items:flex-end;
      border-bottom:1px solid var(--line);
      background:
        linear-gradient(180deg, rgba(7,7,8,.2), rgba(7,7,8,.86)),
        url('<?php echo h($images["hero"]); ?>');
      background-size: cover;
      background-position:center;
    }
    .heroInner{
      width:100%;
      padding: 92px 0 54px;
    }
    .heroGrid{
      display:grid;
      grid-template-columns: 1.2fr .8fr;
      gap:18px;
      align-items:end;
    }
    .kicker{
      font-family:var(--sans);
      letter-spacing:.18em;
      text-transform:uppercase;
      font-size:12px;
      color:rgba(245,242,234,.78);
    }
    h1{
      margin:10px 0 12px;
      font-family:var(--font);
      font-size:46px;
      line-height:1.05;
      letter-spacing:-.02em;
    }
    .lead{
      margin:0 0 16px;
      font-family:var(--sans);
      color:rgba(245,242,234,.80);
      font-size:16px;
      max-width: 64ch;
    }
    .pillRow{display:flex; flex-wrap:wrap; gap:10px; margin-top:12px}
    .pill{
      font-family:var(--sans);
      border:1px solid rgba(214,180,108,.22);
      background:rgba(214,180,108,.06);
      color:rgba(245,242,234,.84);
      padding:8px 10px;
      border-radius:999px;
      font-size:12px;
      letter-spacing:.02em;
    }

    .contactCard h3{
      margin:0 0 10px;
      font-family:var(--font);
      font-size:18px;
      letter-spacing:.01em;
    }
    .meta{
      margin:0;
      font-family:var(--sans);
      color:var(--muted);
      font-size:14px;
    }
    .meta a{color:var(--text); text-decoration:underline; text-underline-offset:3px}
    .meta strong{color:rgba(245,242,234,.92)}

    /* Grids */
    .grid2{display:grid; grid-template-columns: 1fr 1fr; gap:16px}
    .grid3{display:grid; grid-template-columns: repeat(3, 1fr); gap:16px}

    .imgBox{
      position:relative;
      overflow:hidden;
      border-radius:var(--radius);
      border:1px solid var(--line);
    }
    .imgBox img{
      width:100%;
      height:100%;
      object-fit:cover;
      transform: scale(1.01);
    }

    .feature{
      display:flex;
      gap:14px;
      align-items:flex-start;
      padding:16px;
      border-radius:var(--radius);
      border:1px solid var(--line);
      background: rgba(255,255,255,.02);
    }
    .feature strong{
      display:block;
      font-family:var(--font);
      font-size:16px;
      margin-bottom:6px;
      letter-spacing:.01em;
    }
    .feature p{
      margin:0;
      font-family:var(--sans);
      color:var(--muted);
      font-size:14px;
    }

    /* Contact form */
    form{display:grid; gap:12px}
    .row2{display:grid; grid-template-columns: 1fr 1fr; gap:12px}
    label{display:block; margin:0 0 6px; font-family:var(--sans); font-size:12px; color:rgba(245,242,234,.75); letter-spacing:.06em; text-transform:uppercase}
    input, textarea{
      width:100%;
      padding:12px 12px;
      border-radius:14px;
      border:1px solid rgba(245,242,234,.14);
      background: rgba(7,7,8,.40);
      color:var(--text);
      outline:none;
      font-family:var(--sans);
      font-size:14px;
      transition: box-shadow .15s ease, border-color .15s ease;
    }
    textarea{min-height:140px; resize:vertical}
    input:focus, textarea:focus{
      border-color: rgba(214,180,108,.52);
      box-shadow: 0 0 0 3px rgba(214,180,108,.14);
    }
    .help{margin:0; font-family:var(--sans); color:var(--muted); font-size:12px}
    .actions{display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:2px}
    .submit{
      border-color: rgba(214,180,108,.55);
      background: linear-gradient(180deg, rgba(214,180,108,.20), rgba(214,180,108,.10));
    }

    .notice{
      border-radius:16px;
      padding:12px 12px;
      border:1px solid var(--line);
      background: rgba(255,255,255,.02);
      font-family:var(--sans);
      font-size:14px;
      margin-bottom:12px;
    }
    .notice.ok{border-color: rgba(214,180,108,.35); background: rgba(214,180,108,.08)}
    .notice.err{border-color: rgba(255,120,120,.35); background: rgba(255,120,120,.08)}
    .notice ul{margin:8px 0 0 18px}

    /* Footer */
    footer{
      border-top:1px solid var(--line);
      background: rgba(7,7,8,.86);
      padding:26px 0;
    }
    .footerGrid{
      display:flex; align-items:flex-start; justify-content:space-between;
      gap:16px; flex-wrap:wrap;
    }
    .footLinks{display:flex; gap:10px; flex-wrap:wrap}
    .footLinks a{
      font-family:var(--sans);
      color:var(--muted);
      padding:8px 10px;
      border-radius:999px;
      border:1px solid transparent;
    }
    .footLinks a:hover{
      color:var(--text);
      border-color:rgba(214,180,108,.25);
      background:rgba(214,180,108,.06);
    }
    .small{margin:10px 0 0; font-family:var(--sans); color:var(--muted); font-size:13px}

    /* Responsive */
    @media (max-width: 980px){
      .heroGrid{grid-template-columns: 1fr}
      nav ul{display:none}
      .menuBtn{
        display:inline-flex;
        border:1px solid rgba(245,242,234,.14);
        background:rgba(255,255,255,.02);
        padding:10px 12px;
        border-radius:999px;
        font-family:var(--sans);
        color:var(--text);
        cursor:pointer;
      }
      .grid3{grid-template-columns: 1fr}
      .grid2{grid-template-columns: 1fr}
      .row2{grid-template-columns: 1fr}
      h1{font-size:38px}
    }
  </style>
</head>

<body>
  <a class="skip" href="#main">Skip to content</a>

  <header>
    <div class="container">
      <div class="topbar">
        <div class="brand">
          <strong><?php echo h($siteName); ?></strong>
          <span>Imported Luxury Kitchenware</span>
        </div>

        <nav aria-label="Primary navigation">
          <ul id="desktopNav">
            <li><a href="#home" data-link="home">Home</a></li>
            <li><a href="#about" data-link="about">About</a></li>
            <li><a href="#services" data-link="services">Services</a></li>
            <li><a href="#contact" data-link="contact">Contact</a></li>
          </ul>
        </nav>

        <div style="display:flex; gap:10px; align-items:center;">
          <a class="btn" href="#contact">Request Sourcing</a>
          <button class="menuBtn" id="menuBtn" type="button" aria-expanded="false" aria-controls="mobileNav">Menu</button>
        </div>
      </div>

      <div class="mobileNav" id="mobileNav" aria-label="Mobile navigation">
        <a href="#home" data-link="home">Home</a>
        <a href="#about" data-link="about">About</a>
        <a href="#services" data-link="services">Services</a>
        <a href="#contact" data-link="contact">Contact</a>
      </div>
    </div>
  </header>

  <main id="main">

    <!-- HERO / BANNER -->
    <section class="hero" id="home" aria-label="Hero">
      <div class="heroMedia" role="img" aria-label="Luxury kitchenware background image">
        <div class="container heroInner">
          <div class="heroGrid">
            <div>
              <div class="kicker">Curated imports · premium finish · reliable supply</div>
              <h1>Luxury kitchenware that elevates everyday cooking.</h1>
              <p class="lead">
                <?php echo h($siteName); ?> supplies imported, high-end kitchenware for discerning homes,
                interior designers, and hospitality—focused on premium materials, refined aesthetics, and consistent availability.
              </p>
              <div class="pillRow" aria-label="Highlights">
                <span class="pill">Imported collections</span>
                <span class="pill">Premium cookware & tableware</span>
                <span class="pill">Designer & hospitality sourcing</span>
                <span class="pill">Quality-first selection</span>
              </div>
            </div>

            <aside class="panel contactCard">
              <div class="pad">
                <h3>Contact details</h3>
                <p class="meta"><strong>Address:</strong><br><?php echo h($address); ?></p>
                <p class="meta" style="margin-top:10px;"><strong>Phone:</strong><br><a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a></p>
                <p class="meta" style="margin-top:10px;"><strong>Email:</strong><br><a href="mailto:<?php echo h($emailTo); ?>"><?php echo h($emailTo); ?></a></p>
                <p class="meta" style="margin-top:12px;">
                  <strong>Typical requests:</strong><br>
                  curated sets, statement pieces, complete dining collections, and sourcing for premium projects.
                </p>
              </div>
            </aside>

          </div>
        </div>
      </div>
    </section>

    <!-- ABOUT -->
    <section id="about" aria-label="About us">
      <div class="container">
        <div class="sectionTitle">
          <h2>About us</h2>
          <p>Luxury kitchenware supply with a focus on provenance, finishing quality, and consistency.</p>
        </div>

        <div class="grid2">
          <div class="panel">
            <div class="pad">
              <div class="kicker" style="margin-bottom:10px;">Who we are</div>
              <p class="lead" style="margin:0;">
                We source and supply imported premium kitchenware—selected for craftsmanship, design integrity,
                and long-term performance. Our collections are tailored for upscale homes, designers, and boutique hospitality.
              </p>

              <div style="height:14px;"></div>

              <div class="grid3">
                <div class="feature">
                  <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                  <div>
                    <strong>Curated selection</strong>
                    <p>We prioritize materials, finishing, and design coherence—no random inventory.</p>
                  </div>
                </div>
                <div class="feature">
                  <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                  <div>
                    <strong>Import expertise</strong>
                    <p>We help clients select collections aligned with premium positioning and use-case fit.</p>
                  </div>
                </div>
                <div class="feature">
                  <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                  <div>
                    <strong>Reliability</strong>
                    <p>Clear communication, consistent supply, and standards that match high expectations.</p>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <div class="panel">
            <div class="imgBox" style="aspect-ratio:4/3;">
              <img src="<?php echo h($images["about"]); ?>" alt="Luxury kitchenware showroom display" loading="lazy" width="1600" height="1200">
            </div>
            <div class="pad">
              <p class="meta" style="margin:0;">
                From statement cookware to complete dining sets, we support premium projects with sourcing guidance,
                selection notes, and supply planning.
              </p>
            </div>
          </div>
        </div>

        <div style="height:16px;"></div>

        <div class="grid3">
          <div class="panel">
            <div class="imgBox" style="aspect-ratio:1/1;">
              <img src="<?php echo h($images["feature1"]); ?>" alt="Artisan-grade premium kitchenware detail" loading="lazy" width="1200" height="1200">
            </div>
            <div class="pad">
              <strong style="font-family:var(--font); font-size:16px;">Artisan finish</strong>
              <p class="meta" style="margin-top:6px;">Elevated textures, refined colorways, and presentation-ready quality.</p>
            </div>
          </div>

          <div class="panel">
            <div class="imgBox" style="aspect-ratio:1/1;">
              <img src="<?php echo h($images["feature2"]); ?>" alt="Luxury tableware set arranged for dining" loading="lazy" width="1200" height="1200">
            </div>
            <div class="pad">
              <strong style="font-family:var(--font); font-size:16px;">Dining collections</strong>
              <p class="meta" style="margin-top:6px;">Cohesive sets for premium tables—designed to match upscale interiors.</p>
            </div>
          </div>

          <div class="panel">
            <div class="imgBox" style="aspect-ratio:1/1;">
              <img src="<?php echo h($images["feature3"]); ?>" alt="High-end cookware designed for performance" loading="lazy" width="1200" height="1200">
            </div>
            <div class="pad">
              <strong style="font-family:var(--font); font-size:16px;">Performance cookware</strong>
              <p class="meta" style="margin-top:6px;">Premium materials selected for heat control, durability, and longevity.</p>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- SERVICES -->
    <section id="services" aria-label="Services">
      <div class="container">
        <div class="sectionTitle">
          <h2>Services</h2>
          <p>Premium sourcing and supply for residential, design, and hospitality projects.</p>
        </div>

        <div class="grid2">
          <div class="panel">
            <div class="imgBox" style="aspect-ratio:3/2;">
              <img src="<?php echo h($images["svc1"]); ?>" alt="Luxury cookware collection" loading="lazy" width="1800" height="1200">
            </div>
            <div class="pad">
              <strong style="font-family:var(--font); font-size:18px;">Curated product supply</strong>
              <p class="meta" style="margin-top:6px;">
                Access imported luxury cookware, tableware, cutlery, and accessories selected for premium positioning.
              </p>
              <div style="height:10px;"></div>
              <p class="meta" style="margin:0;">
                Ideal for: high-end homes, boutique retail, luxury rentals, and refined hospitality environments.
              </p>
            </div>
          </div>

          <div class="panel">
            <div class="imgBox" style="aspect-ratio:3/2;">
              <img src="<?php echo h($images["svc2"]); ?>" alt="Sourcing and procurement for luxury kitchenware" loading="lazy" width="1800" height="1200">
            </div>
            <div class="pad">
              <strong style="font-family:var(--font); font-size:18px;">Sourcing & project guidance</strong>
              <p class="meta" style="margin-top:6px;">
                We help you select collections by style, material, and use-case—ensuring consistency across a full kitchen and dining setup.
              </p>
              <div style="height:10px;"></div>
              <p class="meta" style="margin:0;">
                Ideal for: interior designers, developers, hospitality purchasing, and premium renovation projects.
              </p>
            </div>
          </div>
        </div>

        <div style="height:16px;"></div>

        <div class="grid2">
          <div class="panel">
            <div class="pad">
              <strong style="font-family:var(--font); font-size:18px;">Fulfillment-ready planning</strong>
              <p class="meta" style="margin-top:6px;">
                Quote preparation, collection mapping, and supply planning for multi-item orders with a premium standard.
              </p>
              <div style="height:12px;"></div>
              <div class="feature">
                <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                <div>
                  <strong>Curated sets</strong>
                  <p>Build cohesive collections: cookware + tableware + accessories.</p>
                </div>
              </div>
              <div style="height:10px;"></div>
              <div class="feature">
                <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                <div>
                  <strong>Premium positioning</strong>
                  <p>Selection aligned with luxury interiors and upscale customer expectations.</p>
                </div>
              </div>
            </div>
          </div>

          <div class="panel">
            <div class="imgBox" style="aspect-ratio:4/5;">
              <img src="<?php echo h($images["tall"]); ?>" alt="Luxury kitchenware accent image" loading="lazy" width="1200" height="1500">
            </div>
            <div class="pad">
              <p class="meta" style="margin:0;">
                If you have a specific aesthetic (modern, classic, minimalist, statement-metal), mention it in your inquiry.
              </p>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" aria-label="Contact">
      <div class="container">
        <div class="sectionTitle">
          <h2>Contact us</h2>
          <p>Tell us what you need—collections, quantities, and desired style direction.</p>
        </div>

        <div class="panel" style="margin-bottom:16px;">
          <div class="imgBox" style="aspect-ratio:16/9;">
            <img src="<?php echo h($images["contact"]); ?>" alt="Luxury kitchen environment banner" loading="lazy" width="1920" height="1080">
          </div>
        </div>

        <div class="grid2">
          <div class="panel">
            <div class="pad">

              <?php if ($success): ?>
                <div class="notice ok" role="status" aria-live="polite">
                  Your message has been sent successfully. We will respond shortly.
                </div>
              <?php endif; ?>

              <?php if ($errors): ?>
                <div class="notice err" role="alert">
                  Please fix the following:
                  <ul>
                    <?php foreach ($errors as $e): ?>
                      <li><?php echo h($e); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <form method="post" action="#contact" novalidate>
                <input type="hidden" name="contact_form" value="1" />
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>" />
                <!-- Honeypot -->
                <input type="text" name="company_website" value="" tabindex="-1" autocomplete="off"
                       style="position:absolute; left:-9999px; width:1px; height:1px;" />

                <div class="row2">
                  <div>
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="<?php echo h($form["name"]); ?>" required />
                  </div>
                  <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?php echo h($form["email"]); ?>" required />
                  </div>
                </div>

                <div class="row2">
                  <div>
                    <label for="phone">Phone (optional)</label>
                    <input id="phone" name="phone" type="text" value="<?php echo h($form["phone"]); ?>" />
                  </div>
                  <div>
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" type="text" value="<?php echo h($form["subject"]); ?>" required />
                  </div>
                </div>

                <div>
                  <label for="message">Message</label>
                  <textarea id="message" name="message" required><?php echo h($form["message"]); ?></textarea>
                  <p class="help">Include: product category, quantity, style preference, and timeline.</p>
                </div>

                <div class="actions">
                  <button class="btn submit" type="submit" id="submitBtn">Send inquiry</button>
                  <a class="btn" href="mailto:<?php echo h($emailTo); ?>" style="border-color:rgba(245,242,234,.14); background:rgba(255,255,255,.02)">Email directly</a>
                  <a class="btn" href="tel:<?php echo h($phone); ?>" style="border-color:rgba(245,242,234,.14); background:rgba(255,255,255,.02)">Call</a>
                </div>
              </form>
            </div>
          </div>

          <div class="panel">
            <div class="pad">
              <strong style="font-family:var(--font); font-size:18px;">Direct contact</strong>
              <p class="meta" style="margin-top:10px;"><strong>Address:</strong><br><?php echo h($address); ?></p>
              <p class="meta" style="margin-top:10px;"><strong>Phone:</strong><br><a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a></p>
              <p class="meta" style="margin-top:10px;"><strong>Email:</strong><br><a href="mailto:<?php echo h($emailTo); ?>"><?php echo h($emailTo); ?></a></p>

              <div style="height:14px;"></div>

              <div class="feature">
                <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                <div>
                  <strong>Fast quoting</strong>
                  <p>Share your desired collection and quantity; we’ll reply with availability and sourcing options.</p>
                </div>
              </div>

              <div style="height:10px;"></div>

              <div class="feature">
                <div style="min-width:10px; height:10px; margin-top:8px; border-radius:999px; background:var(--gold); box-shadow:0 0 0 3px rgba(214,180,108,.12)"></div>
                <div>
                  <strong>Premium projects</strong>
                  <p>Designer and hospitality sourcing supported—cohesive sets and statement pieces.</p>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </section>

  </main>

  <footer>
    <div class="container">
      <div class="footerGrid">
        <div>
          <strong style="font-family:var(--font); font-size:18px;"><?php echo h($siteName); ?></strong>
          <div class="small">
            <?php echo h($address); ?><br>
            <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a> ·
            <a href="mailto:<?php echo h($emailTo); ?>"><?php echo h($emailTo); ?></a>
          </div>
        </div>

        <div class="footLinks" aria-label="Footer navigation">
          <a href="#home" data-link="home">Home</a>
          <a href="#about" data-link="about">About</a>
          <a href="#services" data-link="services">Services</a>
          <a href="#contact" data-link="contact">Contact</a>
          <a href="#home" id="backToTop">Back to top</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    (function () {
      // Mobile menu toggle
      const menuBtn = document.getElementById("menuBtn");
      const mobileNav = document.getElementById("mobileNav");
      if (menuBtn && mobileNav) {
        menuBtn.addEventListener("click", () => {
          const isOpen = mobileNav.style.display === "block";
          mobileNav.style.display = isOpen ? "none" : "block";
          menuBtn.setAttribute("aria-expanded", String(!isOpen));
        });

        // Close mobile nav when a link is clicked
        mobileNav.querySelectorAll('a[href^="#"]').forEach(a => {
          a.addEventListener("click", () => {
            mobileNav.style.display = "none";
            menuBtn.setAttribute("aria-expanded", "false");
          });
        });
      }

      // Active nav highlighting
      const navLinks = Array.from(document.querySelectorAll("[data-link]"));
      const sections = ["home","about","services","contact"]
        .map(id => document.getElementById(id))
        .filter(Boolean);

      function setActive(id) {
        navLinks.forEach(a => a.classList.toggle("active", a.getAttribute("data-link") === id));
      }

      if ("IntersectionObserver" in window) {
        const obs = new IntersectionObserver((entries) => {
          const visible = entries
            .filter(e => e.isIntersecting)
            .sort((a,b) => b.intersectionRatio - a.intersectionRatio)[0];
          if (visible && visible.target && visible.target.id) setActive(visible.target.id);
        }, { threshold: [0.25, 0.5, 0.75] });

        sections.forEach(s => obs.observe(s));
      } else {
        window.addEventListener("scroll", () => {
          let current = "home";
          const y = window.scrollY + 140;
          sections.forEach(s => { if (s.offsetTop <= y) current = s.id; });
          setActive(current);
        });
      }
      setActive(location.hash.replace("#","") || "home");

      // Back to top smooth behavior
      const backToTop = document.getElementById("backToTop");
      if (backToTop) {
        backToTop.addEventListener("click", (e) => {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: "smooth" });
          history.replaceState(null, "", "#home");
        });
      }

      // Prevent double submit
      const form = document.querySelector('form[action="#contact"]');
      const submitBtn = document.getElementById("submitBtn");
      if (form && submitBtn) {
        form.addEventListener("submit", () => {
          submitBtn.disabled = true;
          submitBtn.textContent = "Sending...";
          setTimeout(() => { submitBtn.disabled = false; submitBtn.textContent = "Send inquiry"; }, 8000);
        });
      }
    })();
  </script>
</body>
</html>
