<?php
require_once __DIR__ . '/config/db.php';

$page_title = "Patina Decor | Antique Decor and Wall Art";
$page_desc = "Handcrafted brass wall decor, framed deities and antique pieces, each inspected and ready to hang.";

require_once __DIR__ . '/includes/header.php';

// Fetch categories from DB
$categories = [];
if ($conn) {
    $cat_res = mysqli_query($conn, "
        SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) as item_count
        FROM categories c
        ORDER BY c.category_id ASC
        LIMIT 4
    ");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $categories[] = $row;
        }
    }
}

// Fetch 8 featured / new arrival products from DB
$products = [];
if ($conn) {
    $prod_res = mysqli_query($conn, "
        SELECT p.*, c.category_name,
               (SELECT image_path FROM product_media pm WHERE pm.product_id = p.product_id ORDER BY is_primary DESC, media_id ASC LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    if ($prod_res) {
        while ($row = mysqli_fetch_assoc($prod_res)) {
            $products[] = $row;
        }
    }
}

// Panel style classes array to cycle velvet backgrounds if no image
$panel_classes = ['', 'wine', 'pink'];
?>

<main>
<!-- Hero slider -->
<div class="slider" id="slider" role="region" aria-roledescription="carousel" aria-label="Featured collections">
  <div class="slides" id="slides">
    <div class="slide s1" aria-label="1 of 3">
      <div class="wrap">
        <div>
          <h1>Brass, wood and velvet, made to hang on your wall.</h1>
          <p>Hand-finished deity panels and antique wall decor. Every piece is inspected, framed and shipped ready to hang.</p>
          <div class="cta">
            <a href="products.php" class="btn-brass text-decoration-none">Shop wall decor</a>
            <a href="#featured" class="btn-ghost text-decoration-none">See new arrivals</a>
          </div>
        </div>
        <div class="frame d-none d-lg-block" aria-hidden="true">
            <div class="panel">
            </div>
        </div>
      </div>
    </div>
    <div class="slide s2" aria-label="2 of 3">
      <div class="wrap">
        <div>
          <h2>Temple bells and brass lamps.</h2>
          <p>Antique-finish bells and lamps with a rich, warm tone. Hang one at your entrance or prayer room.</p>
          <div class="cta">
            <a href="products.php?status=Available" class="btn-brass text-decoration-none">Shop bells &amp; lamps</a>
          </div>
        </div>
      </div>
    </div>
    <div class="slide s3" aria-label="3 of 3">
      <div class="wrap">
        <div>
          <h2>Deities for your home shrine.</h2>
          <p>Ganesha, Krishna, Nandi and more in brass and stone. Many pieces are one of a kind.</p>
          <div class="cta">
            <a href="products.php" class="btn-brass text-decoration-none">Shop deities</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <button class="arrow prev" id="prev" aria-label="Previous slide">&#8249;</button>
  <button class="arrow next" id="next" aria-label="Next slide">&#8250;</button>
  <div class="dots" id="dots"></div>
</div>

<!-- Categories -->
<section id="categories" style="padding:80px 0;">
  <div class="wrap">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
      <div>
        <h2 style="font-size:clamp(2rem,4vw,2.9rem);">Shop by collection</h2>
        <p style="color:var(--muted);max-width:52ch;margin-top:8px;">From single brass masks to full gallery walls.</p>
      </div>
      <a href="products.php" class="btn-dark-theme text-decoration-none">View all</a>
    </div>

    <style>
      .cats{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
      .cat{position:relative;aspect-ratio:4/5;padding:24px;display:flex;flex-direction:column;justify-content:flex-end;color:var(--stone);background:var(--oxblood);overflow:hidden;border-radius:2px;text-decoration:none}
      .cat::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 70% 25%,rgba(227,194,121,.45),transparent 45%)}
      .cat:nth-child(2){background:#4a2a1b}.cat:nth-child(3){background:#58203f}.cat:nth-child(4){background:#2d1c12}
      .cat h3{position:relative;font-size:1.7rem;color:var(--stone);margin-bottom:2px}
      .cat span{position:relative;font-size:.9rem;color:#d8c8b6}
      .cat:hover h3{color:var(--brass-lt)}
      @media(max-width:960px){.cats{grid-template-columns:repeat(2,1fr)}}
      @media(max-width:560px){.cats{gap:12px}.cat{padding:16px}.cat h3{font-size:1.3rem}}
    </style>

    <div class="cats">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $index => $cat): ?>
          <a href="products.php?category=<?= urlencode($cat['slug']) ?>" class="cat">
            <h3><?= htmlspecialchars($cat['category_name']) ?></h3>
            <span><?= $cat['item_count'] ?> piece(s)</span>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <a href="products.php" class="cat"><h3>Framed deities</h3><span>24 pieces</span></a>
        <a href="products.php" class="cat"><h3>Bells &amp; lamps</h3><span>18 pieces</span></a>
        <a href="products.php" class="cat"><h3>Brass masks</h3><span>15 pieces</span></a>
        <a href="products.php" class="cat"><h3>Animals &amp; figures</h3><span>21 pieces</span></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Featured New Arrivals -->
<section id="featured" style="padding:0 0 80px 0;">
  <div class="wrap">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
      <div>
        <h2 style="font-size:clamp(2rem,4vw,2.9rem);">New arrivals</h2>
        <p style="color:var(--muted);max-width:52ch;margin-top:8px;">Recently added. Many are one of a kind, so once they are gone they are gone.</p>
      </div>
      <a href="products.php" class="btn-dark-theme text-decoration-none">Shop all</a>
    </div>

    <?php if (empty($products)): ?>
      <div class="p-5 text-center bg-white rounded shadow-sm">
        <h3 class="h5 text-muted mb-2">No 1-of-1 artifacts loaded yet.</h3>
        <p class="text-secondary mb-3">Add artifacts via the admin control panel to populate this gallery.</p>
        <a href="admin/add_product.php" class="btn-brass text-decoration-none">Go to Admin Panel</a>
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($products as $idx => $prod): ?>
          <?php
            $p_class = $panel_classes[$idx % count($panel_classes)];
          ?>
          <article class="card-item">
            <a href="product-detail.php?slug=<?= urlencode($prod['slug']) ?>" class="frame">
              <div class="panel <?= $p_class ?>">
                <?php if (!empty($prod['primary_image'])): ?>
                  <img src="<?= htmlspecialchars($prod['primary_image']) ?>" alt="<?= htmlspecialchars($prod['title']) ?>" loading="lazy" onerror="this.remove()">
                <?php endif; ?>
              </div>
            </a>
            <div class="meta">
              <small><?= htmlspecialchars($prod['category_name'] ?: 'Brass Relic') ?></small>
              <h3>
                <a href="product-detail.php?slug=<?= urlencode($prod['slug']) ?>" class="text-dark text-decoration-none">
                  <?= htmlspecialchars($prod['title']) ?>
                </a>
              </h3>
              <div class="row-meta">
                <span class="price">₹<?= number_format((float)$prod['price'], 0) ?></span>
                <span class="tag <?= $prod['stock_status'] === 'Available' ? 'one' : '' ?>">
                  <?= $prod['stock_status'] === 'Available' ? '1 left' : htmlspecialchars($prod['stock_status']) ?>
                </span>
              </div>
              <a href="product-detail.php?slug=<?= urlencode($prod['slug']) ?>" class="add-btn text-decoration-none">Inspect &amp; Order</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Trust -->
<style>
.trust{background:#fff;border-block:1px solid var(--line);padding:40px 0;}
.trust .wrap{display:grid;grid-template-columns:repeat(3,1fr);gap:40px}
.trust h3{font-size:1.6rem;margin-bottom:8px}
.trust p{color:var(--muted)}
@media(max-width:960px){.trust .wrap{grid-template-columns:1fr;gap:28px}}
</style>
<div class="trust">
  <div class="wrap">
    <div>
      <h3>Inspected by hand</h3>
      <p>Each piece is checked for finish, weight and condition before it is listed, with honest notes on wear.</p>
    </div>
    <div>
      <h3>Ready to hang</h3>
      <p>Panels arrive framed with fittings attached. Hang it the day it arrives.</p>
    </div>
    <div>
      <h3>Packed to arrive safely</h3>
      <p>Double-boxed and fully insured. If anything is damaged in transit, we replace it.</p>
    </div>
  </div>
</div>

<!-- Story -->
<style>
.story{background:var(--oxblood);color:var(--stone);padding:80px 0;}
.story .wrap{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
.story h2{font-size:clamp(2rem,4vw,3rem);margin-bottom:18px}
.story p{color:#d8c8b6;margin-bottom:26px;max-width:52ch}
.pair{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.pair .frame:nth-child(2){margin-top:40px}
@media(max-width:960px){.story .wrap{grid-template-columns:1fr;gap:32px}}
</style>
<section class="story">
  <div class="wrap">
    <div>
      <h2>Build a gallery wall, one panel at a time.</h2>
      <p>Matching frames in wine and plum velvet let brass figures stand out. Start with one panel, or order a set of five and we will send a hanging layout with it.</p>
      <a href="products.php" class="btn-brass text-decoration-none">Browse wall sets</a>
    </div>
    <div class="pair">
      <div class="frame"><div class="panel wine"></div></div>
      <div class="frame"><div class="panel pink"></div></div>
    </div>
  </div>
</section>

<!-- Newsletter -->
<style>
.news{text-align:center;padding:80px 0;}
.news h2{font-size:clamp(1.9rem,4vw,2.6rem);margin-bottom:10px}
.news p{color:var(--muted);margin-bottom:26px}
.news form{display:flex;gap:10px;max-width:480px;margin:0 auto}
.news input{flex:1;padding:14px 16px;border:1.5px solid var(--line);background:#fff;font:inherit;border-radius:2px;min-width:0}
@media(max-width:560px){.news form{flex-direction:column}}
</style>
<section class="news">
  <div class="wrap">
    <h2>Hear about new pieces first</h2>
    <p>Most of our pieces are one of a kind. Get an email when new ones arrive.</p>
    <form onsubmit="event.preventDefault();this.querySelector('button').textContent='Subscribed'">
      <input type="email" placeholder="Your email address" aria-label="Email address" required>
      <button class="btn-brass border-0" type="submit">Subscribe</button>
    </form>
  </div>
</section>
</main>

<!-- Hero Carousel Script -->
<script>
(function(){
  const track = document.getElementById('slides');
  if(!track) return;
  const n = track.children.length;
  const dots = document.getElementById('dots');
  const box = document.getElementById('slider');
  let i = 0, t = null, x0 = null;

  for(let k = 0; k < n; k++){
      const d = document.createElement('button');
      d.setAttribute('aria-label','Go to slide '+(k+1));
      d.onclick = () => go(k);
      dots.appendChild(d);
  }

  function go(k){
      i = (k + n) % n;
      track.style.transform = 'translateX(-' + (i * 100) + '%)';
      [...dots.children].forEach((d, j) => d.setAttribute('aria-current', j === i));
  }

  function play(){
      stop();
      if(!matchMedia('(prefers-reduced-motion:reduce)').matches) {
          t = setInterval(() => go(i + 1), 6000);
      }
  }

  function stop(){ clearInterval(t); }

  const prevBtn = document.getElementById('prev');
  const nextBtn = document.getElementById('next');
  if(prevBtn) prevBtn.onclick = () => go(i - 1);
  if(nextBtn) nextBtn.onclick = () => go(i + 1);

  if(box){
      box.addEventListener('mouseenter', stop);
      box.addEventListener('mouseleave', play);
      box.addEventListener('focusin', stop);
      box.addEventListener('focusout', play);
      box.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; stop(); }, {passive:true});
      box.addEventListener('touchend', e => {
          if(x0 !== null){
              const dx = e.changedTouches[0].clientX - x0;
              if(Math.abs(dx) > 50) go(i + (dx < 0 ? 1 : -1));
              x0 = null;
          }
          play();
      });
  }

  go(0);
  play();
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
