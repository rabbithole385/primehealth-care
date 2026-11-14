<?php
$active = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero__bg" aria-hidden="true"><span class="blob blob--1"></span><span class="blob blob--2"></span><span class="grid-dots"></span></div>
  <div class="wrap hero__inner">
    <div class="hero__copy">
      <p class="eyebrow"><span class="pulse-dot"></span> Atlanta · Miami · Los Angeles · Houston</p>
      <h1>Every life deserves <em>exceptional</em> care.</h1>
      <p class="lead">Prime Care Health Solutions brings medical excellence, compassionate service and a patient-first philosophy together — from everyday check-ups to pharmacy, prevention and coordinated support.</p>
      <div class="hero__actions">
        <a class="btn btn--primary btn--lg" href="<?= url('appointment.php') ?>"><?= icon('calendar', 20) ?> Book an Appointment</a>
        <a class="btn btn--outline btn--lg" href="<?= url('services.php') ?>">Explore Services <?= icon('arrow', 18) ?></a>
      </div>
      <ul class="hero__assure">
        <li><?= icon('check', 18) ?> Care for every age</li>
        <li><?= icon('check', 18) ?> Pharmacy &amp; prevention under one roof</li>
        <li><?= icon('check', 18) ?> Open Saturdays</li>
      </ul>
    </div>

    <div class="hero__visual" aria-hidden="true">
      <div class="hero-art">
        <svg viewBox="0 0 520 560" class="hero-art__svg">
          <defs>
            <linearGradient id="hg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0a52c4"/><stop offset="1" stop-color="#061b4f"/></linearGradient>
            <linearGradient id="hg2" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#00c4f5"/><stop offset="1" stop-color="#8fe3ff"/></linearGradient>
            <clipPath id="arch"><path d="M60 560V230C60 120 150 40 260 40s200 80 200 190v330z"/></clipPath>
          </defs>
          <path d="M60 560V230C60 120 150 40 260 40s200 80 200 190v330z" fill="url(#hg1)"/>
          <g clip-path="url(#arch)" opacity=".18" stroke="#fff" fill="none">
            <circle cx="260" cy="250" r="90"/><circle cx="260" cy="250" r="150"/><circle cx="260" cy="250" r="210"/><circle cx="260" cy="250" r="270"/>
          </g>
          <path class="ecg" d="M60 400h110l22-40 28 80 26-120 26 120 20-40h208" fill="none" stroke="url(#hg2)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="470" cy="400" r="9" fill="#00c4f5"><animate attributeName="r" values="9;14;9" dur="2.4s" repeatCount="indefinite"/></circle>
        </svg>
        <img class="hero-logo" src="<?= url('assets/img/logo-icon.png') ?>" alt="">
        <div class="float-card float-card--a">
          <span class="float-card__icon"><?= icon('heart', 22) ?></span>
          <div><strong>Patient-first care</strong><small>Heard, respected, cared for</small></div>
        </div>
        <div class="float-card float-card--b">
          <span class="float-card__icon float-card__icon--warm"><?= icon('pin', 22) ?></span>
          <div><strong>4 locations</strong><small>GA · FL · CA · TX</small></div>
        </div>
        <div class="float-card float-card--c">
          <span class="float-card__icon"><?= icon('pill', 22) ?></span>
          <div><strong>Pharmacy services</strong><small>Prescriptions &amp; guidance</small></div>
        </div>
      </div>
    </div>
  </div>

  <div class="wrap">
    <form class="finder reveal" action="<?= url('appointment.php') ?>" method="get" aria-label="Quick appointment finder">
      <div class="finder__field">
        <label for="f-service"><?= icon('stethoscope', 18) ?> What do you need?</label>
        <select id="f-service" name="service">
          <option value="">Choose a service</option>
          <?php foreach ($SERVICES as $k => $s): ?><option value="<?= e($k) ?>"><?= e($s['title']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="finder__field">
        <label for="f-location"><?= icon('pin', 18) ?> Where?</label>
        <select id="f-location" name="location">
          <option value="">Choose a location</option>
          <?php foreach ($LOCATIONS as $k => $l): ?><option value="<?= e($k) ?>"><?= e($l['short']) ?>, <?= e($l['state']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn--primary btn--lg" type="submit">Find a time <?= icon('arrow', 18) ?></button>
    </form>
  </div>
</section>

<section class="stats" aria-label="At a glance">
  <div class="wrap stats__grid">
    <div class="stat reveal"><strong data-count="4">4</strong><span>Locations across the United States</span></div>
    <div class="stat reveal"><strong data-count="8">8</strong><span>Areas of care under one roof</span></div>
    <div class="stat reveal"><strong data-count="6">6</strong><span>Days a week our clinics are open</span></div>
    <div class="stat reveal"><strong>1</strong><span>Standard: exceptional care for every life</span></div>
  </div>
</section>

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

<section class="section section--tint">
  <div class="wrap split">
    <div class="split__media reveal">
      <div class="story-art">
        <div class="story-art__panel">
          <p class="eyebrow eyebrow--light">Our story</p>
          <p class="story-art__big">“Every life deserves exceptional care.”</p>
          <p class="story-art__small">The principle Prime Care Health Solutions was founded on.</p>
        </div>
        <div class="story-art__chip"><?= icon('shield', 20) ?> Integrity · Excellence · Compassion</div>
      </div>
    </div>
    <div class="split__copy reveal">
      <p class="eyebrow">A legacy of care</p>
      <h2>More than a hospital — a trusted healthcare destination</h2>
      <p>Prime Care Health Solutions was founded by <strong>Isaiah Washington</strong>, a respected medical professional who believed quality care should treat people with dignity while holding to the highest standards of medical practice.</p>
      <p>Today we extend that commitment beyond hospital-based treatment to preventive care, pharmaceutical services, health education, patient support and coordinated medical solutions.</p>
      <ul class="ticks">
        <li><?= icon('check', 18) ?> Experienced medical professionals</li>
        <li><?= icon('check', 18) ?> Modern healthcare practices</li>
        <li><?= icon('check', 18) ?> A culture of compassion and responsibility</li>
      </ul>
      <a class="btn btn--outline" href="<?= url('about.php') ?>">Read our story <?= icon('arrow', 18) ?></a>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Getting care is simple</p>
      <h2>Your visit in three easy steps</h2>
    </div>
    <ol class="steps">
      <li class="step reveal"><span class="step__n">01</span><h3>Request a visit</h3><p>Choose a service, location and preferred time online — or call us directly. It takes about two minutes.</p></li>
      <li class="step reveal" style="--d:90ms"><span class="step__n">02</span><h3>We confirm with you</h3><p>Our patient support team reaches out by your preferred method to confirm details and answer questions.</p></li>
      <li class="step reveal" style="--d:180ms"><span class="step__n">03</span><h3>Receive exceptional care</h3><p>Meet a team that listens first, explains clearly and coordinates every next step with you.</p></li>
    </ol>
  </div>
</section>

<section class="section section--dark">
  <div class="wrap">
    <div class="section-head section-head--light reveal">
      <p class="eyebrow eyebrow--light">What guides us</p>
      <h2>Our core values</h2>
    </div>
    <div class="values">
      <?php $values = [
        ['heart', 'Compassion', 'We treat every patient with empathy, dignity, and genuine care.'],
        ['star', 'Excellence', 'We pursue the highest standards in medical services, professional conduct, and patient outcomes.'],
        ['scale', 'Integrity', 'We uphold honesty, transparency, accountability, and ethical medical practices.'],
        ['spark', 'Innovation', 'We embrace modern healthcare solutions and continuous improvement to serve our patients better.'],
        ['shield', 'Commitment', 'We remain dedicated to protecting lives, promoting wellness, and building healthier communities.'],
      ]; foreach ($values as $j => [$ic, $t, $d]): ?>
      <div class="value reveal" style="--d:<?= $j * 70 ?>ms">
        <span class="value__icon"><?= icon($ic, 24) ?></span>
        <h3><?= e($t) ?></h3>
        <p><?= e($d) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap founder-teaser reveal">
    <div class="founder-teaser__mono" aria-hidden="true"><span>IW</span></div>
    <figure>
      <span class="founder-teaser__q"><?= icon('quote', 34) ?></span>
      <blockquote>Healthcare is not simply about treating an illness; it is about understanding a human being, restoring hope, preserving dignity, and making a meaningful difference in someone's life.</blockquote>
      <figcaption><strong>Isaiah Washington</strong><span>Founder &amp; Visionary Leader</span></figcaption>
      <a class="btn btn--outline" href="<?= url('founder.php') ?>">Meet our founder <?= icon('arrow', 18) ?></a>
    </figure>
  </div>
</section>

<section class="section section--tint" id="locations">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Find us</p>
      <h2>Care close to home, in four states</h2>
    </div>
    <div class="loc-tabs reveal" data-tabs>
      <div class="loc-tabs__list" role="tablist" aria-label="Locations">
        <?php $first = true; foreach ($LOCATIONS as $k => $l): ?>
        <button role="tab" id="tab-<?= e($k) ?>" aria-controls="panel-<?= e($k) ?>" aria-selected="<?= $first ? 'true' : 'false' ?>" tabindex="<?= $first ? '0' : '-1' ?>">
          <small><?= e($l['badge']) ?></small><strong><?= e($l['short']) ?></strong><span><?= e($l['state']) ?></span>
        </button>
        <?php $first = false; endforeach; ?>
      </div>
      <?php $first = true; foreach ($LOCATIONS as $k => $l): $q = urlencode($l['street'] . ', ' . explode(' ', $l['city'])[0] . ' ' . $l['state']); ?>
      <div class="loc-tabs__panel" role="tabpanel" id="panel-<?= e($k) ?>" aria-labelledby="tab-<?= e($k) ?>"<?= $first ? '' : ' hidden' ?>>
        <div class="loc-info">
          <h3><?= e($l['name']) ?></h3>
          <p class="loc-line"><?= icon('pin', 20) ?> <span><?= e($l['street']) ?><br><?= e($l['city']) ?>, United States</span></p>
          <p class="loc-line"><?= icon('phone', 20) ?> <a href="tel:<?= e($l['tel']) ?>"><?= e($l['phone']) ?></a></p>
          <p class="loc-line"><?= icon('clock', 20) ?> <span><?= e($l['hours']) ?></span></p>
          <div class="loc-info__actions">
            <a class="btn btn--primary" href="<?= url('appointment.php?location=' . $k) ?>">Book here</a>
            <a class="btn btn--outline" href="https://www.google.com/maps/search/?api=1&query=<?= $q ?>" target="_blank" rel="noopener">Directions</a>
          </div>
        </div>
        <div class="loc-map" data-map-src="https://maps.google.com/maps?q=<?= $q ?>&z=13&output=embed" data-map-title="Map of <?= e($l['name']) ?>">
          <span class="loc-map__ph"><?= icon('pin', 28) ?> Loading map…</span>
        </div>
      </div>
      <?php $first = false; endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap faq-wrap">
    <div class="section-head section-head--left reveal">
      <p class="eyebrow">Good to know</p>
      <h2>Frequently asked questions</h2>
      <p>Can't find what you're looking for? <a href="<?= url('contact.php') ?>">Send us a message</a> and our patient support team will help.</p>
    </div>
    <div class="faq reveal">
      <?php $faqs = [
        ['How do I book an appointment?', 'Use our online appointment request form or call the location nearest you. Our team will contact you to confirm a date and time that works.'],
        ['Do you see new patients?', 'Yes. We welcome new patients and families at all four of our locations. Just select "New patient" when you request your appointment.'],
        ['What should I bring to my first visit?', 'Please bring a photo ID, your insurance card if you have one, a list of current medications, and any relevant medical records or test results.'],
        ['Can I fill prescriptions with you?', 'Our pharmaceutical services team can help with prescriptions, refills and medication reviews, working closely with your physician.'],
        ['What if I have a medical emergency?', 'Do not use this website for emergencies. Call 911 immediately or go to the nearest emergency room.'],
        ['Is the information I submit online private?', 'Your details are used only to arrange and confirm your care. Please do not include detailed medical history in online forms — our team will discuss that with you directly.'],
      ]; foreach ($faqs as $n => [$q, $a]): ?>
      <details class="faq__item"<?= $n === 0 ? ' open' : '' ?>>
        <summary><?= e($q) ?><span class="faq__ic"><?= icon('plus', 20) ?></span></summary>
        <div class="faq__body"><p><?= e($a) ?></p></div>
      </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
