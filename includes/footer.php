</div> <!-- /content-wrapper -->

<footer>
  <div class="wrap">
    <div class="fgrid">
      <div>
        <h4>Brass &amp; Heritage</h4>
        <p>Antique and handcrafted brass decor, framed deities, and museum-grade relics shipped across India and worldwide.</p>
      </div>
      <div>
        <h4>Shop</h4>
        <ul>
          <li><a href="products.php">All products</a></li>
          <li><a href="products.php?c=deities">Framed deities</a></li>
          <li><a href="products.php?c=bells">Bells &amp; lamps</a></li>
        </ul>
      </div>
      <div>
        <h4>Help</h4>
        <ul>
          <li><a href="contact.php">Contact us</a></li>
          <li><a href="about.php">Our story</a></li>
          <li><a href="sitemap.php" target="_blank">XML Sitemap</a></li>
        </ul>
      </div>
      <div>
        <h4>Visit</h4>
        <ul>
          <li>Kerala, India</li>
          <li><a href="mailto:hello@brassandheritage.com">hello@brassandheritage.com</a></li>
        </ul>
      </div>
    </div>
    <div class="copy">
      © <?= date('Y') ?> Brass &amp; Heritage. All rights reserved. Authentic 1-of-1 Antique Artifacts.
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const b = document.getElementById('burger');
const m = document.getElementById('menu');
if (b && m) {
    b.addEventListener('click', () => {
        const o = m.classList.toggle('open');
        b.setAttribute('aria-expanded', o);
    });
}
</script>
</body>
</html>
