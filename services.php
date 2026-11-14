<?php
$active = 'services';
$page_title = 'Our Services';
$page_desc = 'Primary and family medicine, preventive care, pharmaceutical services, diagnostics, chronic care, women’s health, pediatrics and patient support at Prime Care Health Solutions.';
require __DIR__ . '/includes/header.php';
$hero = ['eyebrow' => 'Our services', 'title' => 'Care for every stage of <em>life</em>', 'lead' => 'From prevention to treatment to ongoing support — coordinated services delivered by experienced professionals who put people before procedures.', 'crumb' => 'Services'];
require __DIR__ . '/includes/page-hero.php';
?>
<section class="section">
  <div class="wrap svc-layout">
    <nav class="svc-nav reveal" aria-label="Services on this page">
      <p class="eyebrow">Jump to</p>
      <ul>
        <?php foreach ($SERVICES as $k => $s): ?>
        <li><a href="#<?= e($k) ?>" data-spy="<?= e($k) ?>"><?= icon($s['icon'], 18) ?> <?= e($s['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="svc-nav__help">
        <strong>Not sure what you need?</strong>
        <p>Our patient support team can guide you to the right service.</p>
        <a href="<?= url('contact.php') ?>">Ask us <?= icon('arrow', 14) ?></a>
      </div>
    </nav>
    <div class="svc-list">
      <?php $i = 0; foreach ($SERVICES as $k => $s): $i++; ?>
      <article class="svc reveal" id="<?= e($k) ?>">
        <div class="svc__head">
          <span class="svc__icon"><?= icon($s['icon'], 30) ?></span>
          <span class="svc__num"><?= str_pad((string)$i, 2, '0', STR_PAD_LEFT) ?></span>
        </div>
        <h2><?= e($s['title']) ?></h2>
        <p><?= e($s['short']) ?></p>
        <ul class="ticks ticks--grid">
          <?php foreach ($s['points'] as $pt): ?><li><?= icon('check', 18) ?> <?= e($pt) ?></li><?php endforeach; ?>
        </ul>
        <a class="btn btn--outline btn--sm" href="<?= url('appointment.php?service=' . $k) ?>">Book this service <?= icon('arrow', 16) ?></a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
