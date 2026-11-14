<?php
$active = 'locations';
$page_title = 'Our Locations';
$page_desc = 'Visit Prime Care Health Solutions in Atlanta (head office), Miami, Los Angeles and Houston. Addresses, phone numbers, hours and directions.';
require __DIR__ . '/includes/header.php';
$hero = ['eyebrow' => 'Locations', 'title' => 'Four locations. <em>One standard</em> of care.', 'lead' => 'Our head office in Atlanta and branches in Florida, California and Texas share the same patient-first philosophy.', 'crumb' => 'Locations'];
require __DIR__ . '/includes/page-hero.php';
?>
<section class="section">
  <div class="wrap">
    <div class="loc-grid">
      <?php $i = 0; foreach ($LOCATIONS as $k => $l): $q = urlencode($l['street'] . ', ' . explode(' ', $l['city'])[0] . ' ' . $l['state']); ?>
      <article class="loc-card reveal" style="--d:<?= ($i++ % 2) * 90 ?>ms" id="<?= e($k) ?>">
        <div class="loc-map loc-map--card" data-map-src="https://maps.google.com/maps?q=<?= $q ?>&z=13&output=embed" data-map-title="Map of <?= e($l['name']) ?>">
          <span class="loc-map__ph"><?= icon('pin', 28) ?> Loading map…</span>
        </div>
        <div class="loc-card__body">
          <span class="badge<?= $k === 'atlanta' ? ' badge--warm' : '' ?>"><?= e($l['badge']) ?></span>
          <h2><?= e($l['short']) ?>, <?= e($l['state']) ?></h2>
          <p class="loc-card__name"><?= e($l['name']) ?></p>
          <p class="loc-line"><?= icon('pin', 20) ?> <span><?= e($l['street']) ?><br><?= e($l['city']) ?>, United States</span></p>
          <p class="loc-line"><?= icon('phone', 20) ?> <a href="tel:<?= e($l['tel']) ?>"><?= e($l['phone']) ?></a></p>
          <p class="loc-line"><?= icon('clock', 20) ?> <span><?= e($l['hours']) ?></span></p>
          <div class="loc-info__actions">
            <a class="btn btn--primary btn--sm" href="<?= url('appointment.php?location=' . $k) ?>">Book here</a>
            <a class="btn btn--outline btn--sm" href="tel:<?= e($l['tel']) ?>">Call</a>
            <a class="btn btn--text btn--sm" href="https://www.google.com/maps/search/?api=1&query=<?= $q ?>" target="_blank" rel="noopener">Directions <?= icon('arrow', 14) ?></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
