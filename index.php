<?php
$active = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero__bg" aria-hidden="true">
    <span class="blob blob--1"></span>
    <span class="blob blob--2"></span>
    <span class="grid-dots"></span>
  </div>

  <div class="wrap">
    <!-- Epic Hero Slider -->
    <div class="hero-slider" data-hero-slider>
      <div class="hero-slides" data-hero-slides>

        <!-- SLIDE 1: Team & Core Mission -->
        <div class="hero-slide is-active" data-hero-slide>
          <div class="hero-slide__inner">
            <div class="hero__copy">
              <div class="epic-logo-showcase">
                <span class="epic-logo-showcase__mark">
                  <img src="<?= url('assets/img/logo-icon.png') ?>" alt="PrimeCare Emblem">
                </span>
                <span class="epic-logo-showcase__text">
                  <strong>PrimeCare Health Solutions</strong>
                  <small>Excellence · Compassion · Dignity</small>
                </span>
              </div>
              <h1>Every life deserves <em>exceptional</em> care.</h1>
              <p class="lead">Prime Care Health Solutions brings medical excellence, modern diagnostics, and a patient-first philosophy together — under the leadership of a world-class clinical team.</p>
              <div class="hero__actions">
                <a class="btn btn--primary btn--lg" href="<?= url('appointment.php') ?>"><?= icon('calendar', 20) ?> Book an Appointment</a>
                <a class="btn btn--outline btn--lg" href="#doctors">Meet Our Specialists <?= icon('arrow', 18) ?></a>
              </div>
              <ul class="hero__assure">
                <li><?= icon('check', 18) ?> Care for every age</li>
                <li><?= icon('check', 18) ?> Specialized Geriatric &amp; Primary Medicine</li>
                <li><?= icon('check', 18) ?> 4 U.S. Regional Branches</li>
              </ul>
            </div>

            <div class="hero__visual">
              <div class="hero-visual-card">
                <img src="<?= url('assets/img/team-group.jpg') ?>" alt="PrimeCare Executive Medical Team">
                <div class="hero-visual-card__overlay">
                  <span class="hero-visual-card__tag"><?= icon('shield', 14) ?> Clinical Leadership</span>
                  <h4>Executive Medical Board</h4>
                  <p>Physicians, Geriatricians &amp; Clinical Directors at PrimeCare Headquarters</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SLIDE 2: Founder Isaiah Washington, MD -->
        <div class="hero-slide" data-hero-slide>
          <div class="hero-slide__inner">
            <div class="hero__copy">
              <div class="epic-logo-showcase">
                <span class="epic-logo-showcase__mark">
                  <img src="<?= url('assets/img/logo-icon.png') ?>" alt="PrimeCare Emblem">
                </span>
                <span class="epic-logo-showcase__text">
                  <strong>Isaiah Washington, MD</strong>
                  <small>Founder &amp; Chief Executive Officer</small>
                </span>
              </div>
              <h1>A life dedicated to healing. <em>A legacy built on compassion.</em></h1>
              <p class="lead">“Healthcare is not simply about treating an illness; it is about understanding a human being, restoring hope, preserving dignity, and making a meaningful difference in someone’s life.”</p>
              <div class="hero__actions">
                <a class="btn btn--primary btn--lg" href="<?= url('founder.php') ?>">Read Founder's Story <?= icon('arrow', 18) ?></a>
                <a class="btn btn--outline btn--lg" href="<?= url('appointment.php') ?>"><?= icon('calendar', 18) ?> Schedule Consultation</a>
              </div>
              <ul class="hero__assure">
                <li><?= icon('spark', 18) ?> Internal Medicine &amp; Healthcare Visionary</li>
                <li><?= icon('heart', 18) ?> Patient-Centered Philosophy</li>
              </ul>
            </div>

            <div class="hero__visual">
              <div class="hero-visual-card">
                <img src="<?= url('assets/img/founder.jpg') ?>" alt="Isaiah Washington, MD">
                <div class="hero-visual-card__overlay">
                  <span class="hero-visual-card__tag" style="background:#ffbe53;color:#061b4f">Executive Leadership</span>
                  <h4>Isaiah Washington, MD</h4>
                  <p>Founder &amp; Chief Executive Officer · PrimeCare Health Solutions</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SLIDE 3: Specialized Medicine & Clinical Directors -->
        <div class="hero-slide" data-hero-slide>
          <div class="hero-slide__inner">
            <div class="hero__copy">
              <div class="epic-logo-showcase">
                <span class="epic-logo-showcase__mark">
                  <img src="<?= url('assets/img/logo-icon.png') ?>" alt="PrimeCare Emblem">
                </span>
                <span class="epic-logo-showcase__text">
                  <strong>Specialized Clinical Services</strong>
                  <small>Primary, Geriatric &amp; Preventive Care</small>
                </span>
              </div>
              <h1>Connected care, <em>tailored to your life stage.</em></h1>
              <p class="lead">From dedicated senior healthcare and chronic wellness therapies to routine family health assessments, our clinical directors lead collaborative care across all locations.</p>
              <div class="hero__actions">
                <a class="btn btn--primary btn--lg" href="<?= url('card.php') ?>"><?= icon('shield', 18) ?> Digital Patient Cards</a>
                <a class="btn btn--outline btn--lg" href="<?= url('services.php') ?>">Explore All Services <?= icon('arrow', 18) ?></a>
              </div>
              <ul class="hero__assure">
                <li><?= icon('check', 18) ?> Aaliyah Johnson, MD (CMO)</li>
                <li><?= icon('check', 18) ?> Senior &amp; Geriatric Health Center</li>
                <li><?= icon('check', 18) ?> Verified Digital Health Cards</li>
              </ul>
            </div>

            <div class="hero__visual">
              <div class="hero-visual-card">
                <img src="<?= url('assets/img/dr-aaliyah-johnson.jpg') ?>" alt="Aaliyah Johnson, MD, Chief Medical Officer">
                <div class="hero-visual-card__overlay">
                  <span class="hero-visual-card__tag">Clinical Governance</span>
                  <h4>Aaliyah Johnson, MD</h4>
                  <p>Chief Medical Officer · Guiding Healthcare Quality Across All Centers</p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Slider Controls -->
      <div class="hero-controls">
        <div class="hero-dots">
          <button class="hero-dot is-active" data-hero-dot="0" aria-label="Go to slide 1"></button>
          <button class="hero-dot" data-hero-dot="1" aria-label="Go to slide 2"></button>
          <button class="hero-dot" data-hero-dot="2" aria-label="Go to slide 3"></button>
        </div>
        <div class="hero-arrows">
          <button class="hero-arrow" data-hero-prev aria-label="Previous slide"><?= icon('arrow', 16) ?></button>
          <button class="hero-arrow" data-hero-next aria-label="Next slide" style="transform:scaleX(-1)"><?= icon('arrow', 16) ?></button>
        </div>
      </div>
    </div>

    <!-- Quick Finder Form -->
    <form class="finder reveal" action="<?= url('appointment.php') ?>" method="get" aria-label="Quick appointment finder">
      <div class="finder__field">
        <label for="f-service"><?= icon('stethoscope', 18) ?> What do you need?</label>
        <select id="f-service" name="service">
          <option value="">Choose a medical service</option>
          <?php foreach ($SERVICES as $k => $s): ?><option value="<?= e($k) ?>"><?= e($s['title']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="finder__field">
        <label for="f-location"><?= icon('pin', 18) ?> Location</label>
        <select id="f-location" name="location">
          <option value="">Choose a location</option>
          <?php foreach ($LOCATIONS as $k => $l): ?><option value="<?= e($k) ?>"><?= e($l['short']) ?>, <?= e($l['state']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn--primary btn--lg" type="submit">Find Available Times <?= icon('arrow', 18) ?></button>
    </form>
  </div>
</section>

<!-- Stats Bar -->
<section class="stats" aria-label="At a glance">
  <div class="wrap stats__grid">
    <div class="stat reveal"><strong data-count="4">4</strong><span>Regional Branches (GA · FL · CA · TX)</span></div>
    <div class="stat reveal"><strong data-count="6">6</strong><span>Resident Physicians &amp; Medical Directors</span></div>
    <div class="stat reveal"><strong data-count="8">8</strong><span>Comprehensive Care Specialties</span></div>
    <div class="stat reveal"><strong>100%</strong><span>Patient-first clinical focus</span></div>
  </div>
</section>

<!-- All Resident Physicians & Medical Directors Section -->
<section class="section section--tint" id="doctors">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Clinical Leadership</p>
      <h2>Meet our resident physicians &amp; specialists</h2>
      <p>Our board-certified medical doctors bring years of clinical experience, empathy, and specialized diagnostics to every patient visit.</p>
    </div>

    <div class="doctors-grid">
      <?php foreach ($TEAM as $key => $doc): ?>
      <article class="doc-card reveal">
        <div class="doc-card__media">
          <img src="<?= url($doc['image']) ?>" alt="<?= e($doc['name']) ?>">
          <span class="doc-card__badge"><?= e($doc['badge']) ?></span>
        </div>
        <div class="doc-card__body">
          <h3 class="doc-card__name"><?= e($doc['name']) ?></h3>
          <p class="doc-card__role"><?= e($doc['role']) ?></p>
          <span class="doc-card__spec"><?= icon('shield', 14) ?> <?= e($doc['spec']) ?></span>
          <p class="doc-card__bio"><?= e($doc['bio']) ?></p>
          <div class="doc-card__foot">
            <span class="doc-card__status">Accepting Patients</span>
            <a href="<?= url('appointment.php') ?>" class="btn btn--outline btn--sm">Book Visit <?= icon('arrow', 14) ?></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Digital Patient Card Callout Banner -->
<section class="section" style="padding-top:20px;padding-bottom:40px">
  <div class="wrap">
    <div style="background:linear-gradient(135deg, #061b4f 0%, #0a3d82 60%, #0c56b3 100%);border-radius:28px;padding:48px 40px;color:#fff;display:grid;grid-template-columns:1.2fr 0.8fr;gap:40px;align-items:center;box-shadow:var(--sh-2);position:relative;overflow:hidden">
      <div style="position:absolute;right:-60px;bottom:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(0,196,245,0.2) 0%,transparent 70%);pointer-events:none"></div>
      <div>
        <span style="background:rgba(255,255,255,0.15);color:#8fe3ff;padding:5px 14px;border-radius:999px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;display:inline-block;margin-bottom:12px">Patient Identification System</span>
        <h2 style="color:#fff;font-size:2.1rem;margin-bottom:14px">Official PrimeCare Digital Health Cards</h2>
        <p style="color:#c9d6f0;font-size:1.02rem;line-height:1.6;margin-bottom:24px">Every registered patient at PrimeCare receives an authorized Medical ID card. Check your verification status, view your card online, or present it at any of our four branch locations.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a class="btn btn--primary" href="<?= url('card.php') ?>"><?= icon('shield', 18) ?> Verify / View Health Card</a>
          <a class="btn btn--outline" href="<?= url('appointment.php') ?>" style="color:#fff;border-color:rgba(255,255,255,0.3)">Register as New Patient</a>
        </div>
      </div>
      <div style="background:rgba(255,255,255,0.08);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.2);border-radius:20px;padding:28px;text-align:center">
        <div style="font-size:3rem;margin-bottom:8px">💳</div>
        <strong style="display:block;font-size:1.2rem;color:#fff;margin-bottom:6px">Instant Card Verification</strong>
        <p style="font-size:.86rem;color:#9fb2d8;margin-bottom:18px">Enter your assigned Card ID (e.g. PC-2026-XXXX) to access your verified profile.</p>
        <form action="<?= url('card.php') ?>" method="get" style="display:flex;gap:8px">
          <input type="text" name="id" placeholder="PC-2026-XXXX" required style="width:100%;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.9);color:#061b4f;font-weight:700;text-transform:uppercase;font-size:.9rem">
          <button type="submit" class="btn btn--primary btn--sm" style="flex-shrink:0">Verify</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Services Grid Section -->
<section class="section" id="services">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">What we do</p>
      <h2>Comprehensive care, coordinated around you</h2>
      <p>Healthcare isn't only about treating illness — it's about protecting lives and promoting wellness. Our services work together so your care feels connected, not fragmented.</p>
    </div>
    <div class="cards">
      <?php $i = 0; foreach ($SERVICES as $k => $s): ?>
      <a class="card reveal" style="--d:<?= ($i++ % 4) * 70 ?>ms" href="<?= url('services.php#' . $k) ?>">
        <span class="card__icon"><?= icon($s['icon'], 26) ?></span>
        <h3><?= e($s['title']) ?></h3>
        <p><?= e($s['short']) ?></p>
        <span class="card__link">Learn more <?= icon('arrow', 16) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Founder Quote Section -->
<section class="section">
  <div class="wrap founder-teaser reveal">
    <div class="founder-teaser__mono"><img src="<?= url('assets/img/founder.jpg') ?>" alt="Isaiah Washington, MD"></div>
    <figure>
      <span class="founder-teaser__q"><?= icon('quote', 34) ?></span>
      <blockquote>Healthcare is not simply about treating an illness; it is about understanding a human being, restoring hope, preserving dignity, and making a meaningful difference in someone's life.</blockquote>
      <figcaption><strong>Isaiah Washington, MD</strong><span>Founder &amp; Chief Executive Officer</span></figcaption>
      <a class="btn btn--outline" href="<?= url('founder.php') ?>">Meet our founder <?= icon('arrow', 18) ?></a>
    </figure>
  </div>
</section>

<!-- Locations Section -->
<section class="section section--tint" id="locations">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Find us</p>
      <h2>Four welcoming locations, one standard of care</h2>
      <p>Whether you visit us in Atlanta, Miami, Los Angeles, or Houston, you will find experienced physicians, modern facilities, and a dedicated team.</p>
    </div>

    <div class="loc-tabs reveal" data-tabs>
      <div class="loc-tabs__list" role="tablist" aria-label="Locations">
        <?php $first = true; foreach ($LOCATIONS as $k => $l): ?>
        <button role="tab" id="tab-<?= e($k) ?>" aria-controls="panel-<?= e($k) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>" tabindex="<?= $first ? '0' : '-1' ?>" data-tab="<?= e($k) ?>">
          <span class="loc-tabs__tag"><?= e($l['badge']) ?></span>
          <strong><?= e($l['name']) ?></strong>
          <small><?= e($l['state']) ?></small>
        </button>
        <?php $first = false; endforeach; ?>
      </div>

      <?php $first = true; foreach ($LOCATIONS as $k => $l): ?>
      <div class="loc-tabs__panel<?= $first ? ' is-active' : '' ?>" id="panel-<?= e($k) ?>" role="tabpanel" aria-labelledby="tab-<?= e($k) ?>" <?= $first ? '' : 'hidden' ?> data-panel="<?= e($k) ?>">
        <div class="loc-info">
          <p class="eyebrow"><?= e($l['badge']) ?></p>
          <h3><?= e($l['name']) ?></h3>
          <p class="loc-addr"><?= icon('pin', 18) ?> <span><?= e($l['street']) ?><br><?= e($l['city']) ?></span></p>
          <p class="loc-phone"><?= icon('phone', 18) ?> <a href="tel:<?= e($l['tel']) ?>"><?= e($l['phone']) ?></a></p>
          <p class="loc-hours"><?= icon('clock', 18) ?> <span><?= e($l['hours']) ?></span></p>
          <div class="loc-actions">
            <a class="btn btn--primary" href="<?= url('appointment.php?location=' . $k) ?>"><?= icon('calendar', 18) ?> Book at this clinic</a>
            <a class="btn btn--outline" href="https://maps.google.com/?q=<?= urlencode($l['street'] . ', ' . $l['city']) ?>" target="_blank" rel="noopener">Directions <?= icon('arrow', 16) ?></a>
          </div>
        </div>
        <div class="loc-map" data-map-src="https://www.google.com/maps?q=<?= urlencode($l['street'] . ', ' . $l['city']) ?>&amp;output=embed" data-map-title="Map of <?= e($l['name']) ?>">
          <div class="loc-map__ph"><span class="pulse-dot"></span> Loading interactive map…</div>
        </div>
      </div>
      <?php $first = false; endforeach; ?>
    </div>
  </div>
</section>

<!-- 3 Steps -->
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Getting care is simple</p>
      <h2>Your visit in three easy steps</h2>
    </div>
    <ol class="steps">
      <li class="step reveal"><span class="step__n">01</span><h3>Request a visit</h3><p>Choose a service, location and preferred time online — or call us directly. It takes about two minutes.</p></li>
      <li class="step reveal" style="--d:90ms"><span class="step__n">02</span><h3>We confirm with you</h3><p>Our patient support team reaches out by your preferred method to confirm details and answer questions.</p></li>
      <li class="step reveal" style="--d:180ms"><span class="step__n">03</span><h3>Receive unhurried care</h3><p>Meet with a clinician who listens, explains clearly, and gives you a personalised care plan.</p></li>
    </ol>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
