<?php
http_response_code(404);
$page_title = 'Page not found';
require __DIR__ . '/includes/header.php';
?>
<section class="section notfound">
  <div class="wrap notfound__inner">
    <p class="notfound__code">404</p>
    <h1>We couldn't find that page.</h1>
    <p class="lead">The page may have moved. Let's get you back on track.</p>
    <div class="hero__actions">
      <a class="btn btn--primary" href="<?= url('index.php') ?>">Go to homepage</a>
      <a class="btn btn--outline" href="<?= url('appointment.php') ?>">Book an appointment</a>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
