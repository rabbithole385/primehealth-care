<?php /* expects $hero = ['eyebrow'=>, 'title'=>, 'lead'=>, 'crumb'=>] */ ?>
<section class="page-hero">
  <div class="hero__bg" aria-hidden="true"><span class="blob blob--1"></span><span class="grid-dots"></span></div>
  <div class="wrap page-hero__inner">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page"><?= e($hero['crumb']) ?></span></nav>
    <p class="eyebrow"><?= e($hero['eyebrow']) ?></p>
    <h1><?= $hero['title'] ?></h1>
    <?php if (!empty($hero['lead'])): ?><p class="lead"><?= e($hero['lead']) ?></p><?php endif; ?>
  </div>
</section>
