<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($page_title)) {
    $page_title = "Patina Decor | Antique Decor and Wall Art";
}
if (!isset($page_desc)) {
    $page_desc = "Handcrafted brass wall decor, framed deities and patina-rich antique pieces, each inspected and ready to hang.";
}
if (!isset($og_image)) {
    $og_image = "https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80";
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$current_url = $protocol . $host . ($_SERVER['REQUEST_URI'] ?? '');

$is_user_logged = isset($_SESSION['user_id']);
$user_name = $_SESSION['full_name'] ?? '';
$is_admin_user = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($page_desc) ?>">

<!-- Open Graph (OG) Meta Tags for Social Media & Search Engine Crawling -->
<meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($page_desc) ?>">
<meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
<meta property="og:url" content="<?= htmlspecialchars($current_url) ?>">
<meta property="og:type" content="product">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($page_desc) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{
  --oxblood:#3b0f14; --velvet:#6b1a22; --plum:#8a3b63; --walnut:#4a2a1b;
  --brass:#b8893b; --brass-lt:#e3c279; --stone:#f2eee8; --ink:#221a17; --muted:#6d625b; --line:#ddd5ca;
  --serif:'Playfair Display','Georgia',serif; --sans:'Inter','Segoe UI',Arial,sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--stone);color:var(--ink);line-height:1.6;-webkit-font-smoothing:antialiased;display:flex;flex-direction:column;min-height:100vh}
.content-wrapper{flex:1}
img{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
:focus-visible{outline:3px solid var(--brass);outline-offset:3px}
.wrap{max-width:1200px;margin:0 auto;padding:0 24px}
h1,h2,h3{font-family:var(--serif);font-weight:600;line-height:1.2;letter-spacing:-.01em}
.btn-brass{background:var(--brass);color:var(--oxblood);font-weight:600;border:1.5px solid var(--brass);border-radius:2px;padding:12px 24px;transition:all .2s;font-family:var(--sans)}
.btn-brass:hover{background:var(--brass-lt);color:var(--oxblood)}
.btn-ghost{color:var(--brass-lt);border:1.5px solid var(--brass-lt);border-radius:2px;padding:12px 24px;font-weight:600;transition:all .2s;font-family:var(--sans)}
.btn-ghost:hover{background:rgba(227,194,121,.12);color:var(--brass-lt)}
.btn-dark-theme{border:1.5px solid var(--oxblood);color:var(--oxblood);font-weight:600;border-radius:2px;padding:12px 24px;transition:all .2s;font-family:var(--sans);background:transparent}
.btn-dark-theme:hover{background:var(--oxblood);color:var(--stone)}

/* Top bar and nav */
.topbar{background:#240809;color:var(--brass-lt);font-size:.85rem;text-align:center;padding:8px 16px}
header{background:var(--oxblood);color:var(--stone);position:sticky;top:0;z-index:1020;border-bottom:1px solid rgba(227,194,121,.25)}
.nav-container{display:flex;align-items:center;justify-content:space-between;height:72px;gap:24px}
.logo{font-family:var(--serif);font-size:1.5rem;font-weight:700;letter-spacing:.02em;color:var(--stone)}
.logo span{color:var(--brass-lt)}
.menu-nav{display:flex;gap:28px;list-style:none;font-size:.95rem;margin:0;padding:0;align-items:center}
.menu-nav a{padding:6px 0;border-bottom:2px solid transparent;color:var(--stone);text-decoration:none}
.menu-nav a:hover,.menu-nav a.on{border-color:var(--brass);color:var(--brass-lt)}
.tools{display:flex;gap:14px;align-items:center;font-size:.95rem}
.burger{display:none;background:none;border:0;color:var(--stone);font-size:1.7rem;cursor:pointer}

/* Hero slider */
.slider{position:relative;overflow:hidden;background:var(--oxblood);color:var(--stone)}
.slides{display:flex;transition:transform .8s cubic-bezier(.65,0,.35,1)}
.slide{flex:0 0 100%;min-height:clamp(500px,75vh,680px);display:flex;align-items:center;background-size:cover;background-position:center}
.slide.s1{background:radial-gradient(1200px 500px at 85% 40%,#5a1620 0%,var(--oxblood) 60%)}
.slide.s2{background-image:linear-gradient(90deg,rgba(36,8,9,.92) 35%,rgba(36,8,9,.35)),url('https://images.unsplash.com/photo-1767274547012-b6dd51c06cb5?auto=format&fit=crop&w=1600&q=70')}
.slide.s3{background-image:linear-gradient(90deg,rgba(36,8,9,.92) 35%,rgba(36,8,9,.35)),url('https://images.unsplash.com/photo-1771414092662-1b2e63db5973?auto=format&fit=crop&w=1600&q=70')}
.slide .wrap{width:100%;display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center;padding-top:48px;padding-bottom:72px}
.slide.s2 .wrap,.slide.s3 .wrap{grid-template-columns:minmax(0,640px)}
.slide h1,.slide h2{font-size:clamp(2.3rem,5vw,3.9rem);margin-bottom:20px}
.slide p{color:#e3d7ca;max-width:48ch;font-size:1.08rem;margin-bottom:32px}
.cta{display:flex;gap:14px;flex-wrap:wrap}
.slide .frame{max-width:360px;margin-left:auto;transform:rotate(1.5deg)}
.slide .relic{width:52%}
.arrow{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:50%;border:1.5px solid rgba(227,194,121,.6);background:rgba(36,8,9,.55);color:var(--brass-lt);font-size:1.3rem;cursor:pointer;z-index:5;transition:background .2s;display:grid;place-items:center}
.arrow:hover{background:var(--brass);color:var(--oxblood)}
.arrow.prev{left:20px}.arrow.next{right:20px}
.dots{position:absolute;bottom:28px;left:0;right:0;display:flex;justify-content:center;gap:10px;z-index:5}
.dots button{width:34px;height:4px;border:0;background:rgba(255,255,255,.35);cursor:pointer;padding:0}
.dots button[aria-current="true"]{background:var(--brass-lt)}

/* Framed piece: wooden frame + velvet panel + brass relic */
.frame{background:linear-gradient(135deg,#7a4526,var(--walnut));padding:14px;border:3px solid #150806;box-shadow:0 24px 40px rgba(0,0,0,.35),inset 0 0 0 2px rgba(255,255,255,.08);display:block}
.panel{aspect-ratio:3/4;background:
  radial-gradient(circle at 30% 20%,rgba(255,255,255,.1),transparent 40%),
  repeating-radial-gradient(circle at 50% 50%,rgba(0,0,0,.12) 0 2px,transparent 2px 5px),var(--velvet);
  display:grid;place-items:center;border:2px solid #150806;position:relative;overflow:hidden}
.panel.pink{background:radial-gradient(circle at 30% 20%,rgba(255,255,255,.25),transparent 45%),var(--plum)}
.panel.wine{background:radial-gradient(circle at 30% 20%,rgba(255,255,255,.1),transparent 40%),#4a1219}
.panel img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.relic{width:46%;aspect-ratio:1;border-radius:50% 50% 46% 46%;background:radial-gradient(circle at 35% 30%,#f6e2a8,var(--brass) 55%,#6e4d17);display:grid;place-items:center;font-family:var(--serif);font-size:clamp(2rem,5vw,3.4rem);color:#4a3210;box-shadow:0 10px 18px rgba(0,0,0,.45);z-index:1}

/* Grid & Cards */
.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:28px}
.card-item{display:flex;flex-direction:column}
.card-item .frame{padding:10px;box-shadow:0 10px 18px rgba(0,0,0,.18)}
.card-item .panel{aspect-ratio:4/5}
.card-item .relic{width:55%;font-size:1.8rem}
.meta{padding:16px 2px 0}
.meta small{color:var(--muted);font-size:.85rem}
.meta h3{font-size:1.25rem;margin:2px 0 8px;font-family:var(--serif)}
.row-meta{display:flex;justify-content:space-between;align-items:center}
.price{font-weight:600;font-size:1.05rem}
.tag{font-size:.78rem;background:var(--oxblood);color:var(--brass-lt);padding:3px 10px;border-radius:20px}
.tag.one{background:var(--brass);color:var(--oxblood)}
.add-btn{margin-top:12px;width:100%;padding:11px;background:transparent;border:1.5px solid var(--oxblood);color:var(--oxblood);font-weight:600;cursor:pointer;font-family:var(--sans);transition:.2s;text-align:center;display:block}
.add-btn:hover{background:var(--oxblood);color:var(--stone)}

/* Footer */
footer{background:#1b0708;color:#b9a898;padding:56px 0 28px;font-size:.92rem}
.fgrid{display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:40px;margin-bottom:36px}
footer h4{font-family:var(--serif);font-size:1.2rem;color:var(--brass-lt);margin-bottom:12px}
footer li{list-style:none;margin-bottom:8px}
footer a{color:#b9a898;text-decoration:none}
footer a:hover{color:var(--brass-lt)}
.copy{border-top:1px solid rgba(255,255,255,.1);padding-top:20px;text-align:center}

@media(max-width:960px){
  .slide .wrap{grid-template-columns:1fr;gap:32px}
  .slide .frame{display:none}
  .arrow{display:none}
  .grid{grid-template-columns:repeat(2,1fr)}
  .fgrid{grid-template-columns:1fr 1fr}
  .burger{display:block}
  .menu-nav{display:none;position:absolute;top:72px;left:0;right:0;background:var(--oxblood);flex-direction:column;gap:0;padding:8px 24px 20px;border-bottom:1px solid rgba(227,194,121,.25)}
  .menu-nav.open{display:flex}
  .menu-nav a{display:block;padding:12px 0}
  .tools .hide-s{display:none}
}
@media(max-width:560px){
  .grid{gap:16px}
  .fgrid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="topbar">Free insured shipping across India on orders above ₹5,000</div>

<header>
  <div class="wrap nav-container">
    <a href="index.php" class="logo">Patina <span>Decor</span></a>

    <ul class="menu-nav" id="menu">
      <li><a class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'on' : '' ?>" href="index.php">Home</a></li>
      <li><a class="<?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'on' : '' ?>" href="products.php">Shop all</a></li>
      <li><a href="index.php#categories">Collections</a></li>
      <li><a class="<?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'on' : '' ?>" href="about.php">Our story</a></li>
      <li><a class="<?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'on' : '' ?>" href="contact.php">Contact</a></li>
    </ul>

    <div class="tools">
      <?php if ($is_user_logged): ?>
        <span class="text-light small d-none d-md-inline me-1">Hi, <strong><?= htmlspecialchars($user_name) ?></strong></span>
        <?php if ($is_admin_user): ?>
            <a href="admin/dashboard.php" class="btn btn-sm btn-warning fw-bold text-dark me-1">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" class="btn btn-sm btn-outline-light me-1">Sign Out</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-sm btn-outline-light me-1">Sign In</a>
        <a href="register.php" class="btn btn-sm btn-warning text-dark fw-bold me-1">Register</a>
      <?php endif; ?>
      <a href="sitemap.php" class="btn btn-sm btn-outline-secondary text-light hide-s" target="_blank">XML Sitemap</a>
      <button class="burger" id="burger" aria-label="Open menu" aria-expanded="false" aria-controls="menu">☰</button>
    </div>
  </div>
</header>

<div class="content-wrapper">
