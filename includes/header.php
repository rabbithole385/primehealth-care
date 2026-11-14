<?php
require_once __DIR__ . '/functions.php';
$page_title = $page_title ?? SITE_NAME;
$page_desc  = $page_desc ?? 'Prime Care Health Solutions delivers compassionate, comprehensive healthcare across Atlanta, Miami, Los Angeles and Houston — primary care, preventive care, pharmacy services and patient support.';
$active     = $active ?? '';
$full_title = $page_title === SITE_NAME ? SITE_NAME . ' — ' . SITE_TAGLINE : $page_title . ' | ' . SITE_NAME;
$nav = [
    'home'      => ['Home', 'index.php'],
    'about'     => ['About', 'about.php'],
    'founder'   => ['Our Founder', 'founder.php'],
    'services'  => ['Services', 'services.php'],
    'locations' => ['Locations', 'locations.php'],
    'card'      => ['Patient Cards', 'card.php'],
    'contact'   => ['Contact', 'contact.php'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<script>document.documentElement.classList.add('js')</script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($full_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta name="theme-color" content="#0033a0">
<meta property="og:title" content="<?= e($full_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e(SITE_URL) ?>">
<meta property="og:image" content="<?= e(SITE_URL) ?>/assets/img/logo-full.png">
<link rel="icon" href="<?= url('assets/img/favicon.png') ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= url('assets/img/apple-touch-icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=1.0">
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'MedicalOrganization',
    'name' => SITE_NAME,
    'url' => SITE_URL,
    'slogan' => SITE_TAGLINE,
    'founder' => ['@type' => 'Person', 'name' => 'Isaiah Washington'],
    'location' => array_values(array_map(fn($l) => [
        '@type' => 'Place', 'name' => $l['name'], 'telephone' => $l['phone'],
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $l['street'], 'addressLocality' => explode(',', $l['city'])[0], 'addressCountry' => 'US'],
    ], $GLOBALS['LOCATIONS'])),
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
</head>
<body class="page-<?= e($active ?: 'default') ?>">
<a class="skip" href="#main">Skip to main content</a>

<div class="topbar">
  <div class="wrap topbar__inner">
    <p class="topbar__alert"><?= icon('alert', 16) ?> <span><strong>Medical emergency?</strong> Call <a href="tel:911">911</a> or go to your nearest emergency room.</span></p>
    <div class="topbar__right">
      <span class="topbar__hours"><?= icon('clock', 15) ?> Mon–Fri 8am–6pm · Sat 9am–2pm</span>
      <div class="loc-picker" data-loc-picker>
        <button class="loc-picker__btn" type="button" aria-haspopup="true" aria-expanded="false">
          <?= icon('phone', 15) ?> <span><span data-loc-label>Atlanta</span>:</span> <strong data-loc-phone><?= e($LOCATIONS['atlanta']['phone']) ?></strong>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <ul class="loc-picker__menu" role="menu">
          <?php foreach ($LOCATIONS as $k => $l): ?>
          <li role="none"><button role="menuitem" type="button" data-loc="<?= e($k) ?>" data-label="<?= e($l['short']) ?>" data-phone="<?= e($l['phone']) ?>" data-tel="<?= e($l['tel']) ?>">
            <span><?= e($l['short']) ?>, <?= e($l['state']) ?></span><small><?= e($l['phone']) ?></small>
          </button></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<header class="header" data-header>
  <div class="wrap header__inner">
    <a href="<?= url('index.php') ?>" class="brand" aria-label="<?= e(SITE_NAME) ?> home">
      <span class="brand__mark"><?php include __DIR__ . '/logo-mark.php'; ?></span>
      <span class="brand__text"><strong>Prime<span>Care</span></strong><small>Health Solutions</small></span>
    </a>
    <nav class="nav" aria-label="Main">
      <ul>
        <?php foreach ($nav as $k => [$label, $href]): ?>
        <li><a href="<?= url($href) ?>"<?= $active === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <div class="header__cta">
      <a class="btn btn--primary" href="<?= url('appointment.php') ?>"><?= icon('calendar', 18) ?> <span>Book Appointment</span></a>
      <button class="burger" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="drawer" data-burger><?= icon('menu') ?></button>
    </div>
  </div>
</header>

<div class="drawer" id="drawer" hidden data-drawer>
  <div class="drawer__panel">
    <div class="drawer__head">
      <span class="brand__text"><strong>Prime<span>Care</span></strong><small>Health Solutions</small></span>
      <button type="button" class="drawer__close" aria-label="Close menu" data-drawer-close><?= icon('close') ?></button>
    </div>
    <nav aria-label="Mobile">
      <?php foreach ($nav as $k => [$label, $href]): ?>
      <a href="<?= url($href) ?>"<?= $active === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> <?= icon('arrow', 18) ?></a>
      <?php endforeach; ?>
    </nav>
    <a class="btn btn--primary btn--block" href="<?= url('appointment.php') ?>"><?= icon('calendar', 18) ?> Book Appointment</a>
    <div class="drawer__phones">
      <p class="eyebrow">Call a location</p>
      <?php foreach ($LOCATIONS as $l): ?>
      <a href="tel:<?= e($l['tel']) ?>"><span><?= e($l['short']) ?></span><strong><?= e($l['phone']) ?></strong></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<main id="main">
