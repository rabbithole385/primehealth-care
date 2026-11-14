</main>

<section class="cta-band" aria-labelledby="cta-band-title">
  <div class="wrap cta-band__inner reveal">
    <div>
      <p class="eyebrow eyebrow--light">We're here for you</p>
      <h2 id="cta-band-title">Ready to take the next step in your care?</h2>
      <p>Request an appointment online in under two minutes, or call the location nearest you. Our team will confirm your visit quickly.</p>
    </div>
    <div class="cta-band__actions">
      <a class="btn btn--light" href="<?= url('appointment.php') ?>"><?= icon('calendar', 18) ?> Request Appointment</a>
      <a class="btn btn--ghost-light" href="<?= url('locations.php') ?>"><?= icon('pin', 18) ?> Find a Location</a>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="wrap footer__grid">
    <div class="footer__brand">
      <a href="<?= url('index.php') ?>" class="brand brand--light">
        <span class="brand__mark"><?php include __DIR__ . '/logo-mark.php'; ?></span>
        <span class="brand__text"><strong>Prime<span>Care</span></strong><small>Health Solutions</small></span>
      </a>
      <p>A legacy of care. A commitment to life. Comprehensive, compassionate healthcare for individuals and families across four states.</p>
      <p class="footer__tag"><?= e(SITE_TAGLINE) ?></p>
    </div>
    <div>
      <h3>Explore</h3>
      <ul>
        <li><a href="<?= url('about.php') ?>">About Us</a></li>
        <li><a href="<?= url('founder.php') ?>">Meet Our Founder</a></li>
        <li><a href="<?= url('services.php') ?>">Our Services</a></li>
        <li><a href="<?= url('locations.php') ?>">Locations</a></li>
        <li><a href="<?= url('appointment.php') ?>">Book Appointment</a></li>
        <li><a href="<?= url('contact.php') ?>">Contact Us</a></li>
      </ul>
    </div>
    <div class="footer__locs">
      <h3>Our Locations</h3>
      <ul>
        <?php foreach ($LOCATIONS as $l): ?>
        <li>
          <strong><?= e($l['short']) ?>, <?= e($l['state']) ?></strong>
          <span><?= e($l['street']) ?></span>
          <a href="tel:<?= e($l['tel']) ?>"><?= e($l['phone']) ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h3>Health Updates</h3>
      <p>Wellness tips and clinic news, occasionally. No spam.</p>
      <form class="newsletter" method="post" action="<?= url('subscribe.php') ?>" data-ajax-form>
        <?= csrf_field() ?>
        <label class="sr-only" for="nl-email">Email address</label>
        <input id="nl-email" type="email" name="email" placeholder="you@example.com" required autocomplete="email">
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button class="btn btn--primary" type="submit" aria-label="Subscribe"><?= icon('arrow', 18) ?></button>
        <p class="form-msg" role="status" aria-live="polite"></p>
      </form>
      <p class="footer__emergency"><?= icon('alert', 16) ?> In an emergency, always call <a href="tel:911">911</a>.</p>
    </div>
  </div>
  <div class="wrap footer__bottom">
    <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
    <p class="footer__disclaimer">Information on this website is for general purposes and is not a substitute for professional medical advice.</p>
    <p>Powered by <a href="https://digitalweboracleict.com" target="_blank" rel="noopener">DWO</a></p>
  </div>
</footer>

<nav class="mobilebar" aria-label="Quick actions">
  <a href="tel:<?= e($LOCATIONS['atlanta']['tel']) ?>" data-mobile-call><?= icon('phone', 20) ?><span>Call</span></a>
  <a href="<?= url('appointment.php') ?>" class="mobilebar__main"><?= icon('calendar', 20) ?><span>Book</span></a>
  <a href="<?= url('locations.php') ?>"><?= icon('pin', 20) ?><span>Locations</span></a>
</nav>

<button class="totop" type="button" aria-label="Back to top" data-totop><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg></button>

<script src="<?= url('assets/js/main.js') ?>?v=1.0" defer></script>
</body>
</html>
