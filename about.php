<?php
$active = 'about';
$page_title = 'About Us';
$page_desc = 'A legacy of care and a commitment to life. Learn the story, mission, vision and values behind Prime Care Health Solutions.';
require __DIR__ . '/includes/header.php';
$hero = ['eyebrow' => 'About Prime Care Health Solutions', 'title' => 'A Legacy of Care.<br><em>A Commitment to Life.</em>', 'lead' => 'An institution built around one fundamental principle: every life deserves exceptional care.', 'crumb' => 'About'];
require __DIR__ . '/includes/page-hero.php';
?>
<section class="section">
  <div class="wrap prose-split">
    <aside class="prose-split__side reveal">
      <p class="eyebrow">Our story</p>
      <h2>Where it began</h2>
      <div class="side-card">
        <p><strong>Founder &amp; CEO</strong><br>Dr. Isaiah Washington</p>
        <p><strong>Head office</strong><br>Atlanta, Georgia</p>
        <p><strong>Branches</strong><br>Miami · Los Angeles · Houston</p>
        <a class="btn btn--outline btn--sm" href="<?= url('founder.php') ?>">Meet our founder <?= icon('arrow', 16) ?></a>
      </div>
    </aside>
    <div class="prose reveal">
      <p class="dropcap">Prime Care Health Solutions was founded by Dr. Isaiah Washington, a highly respected medical professional whose passion for healthcare, dedication to human well-being, and belief in accessible, quality medical services inspired the establishment of an institution built around one fundamental principle: every life deserves exceptional care.</p>
      <p>The story of Prime Care Health Solutions began with a vision to redefine the healthcare experience by bringing together medical excellence, compassionate service, innovation, and a patient-first philosophy. Recognizing the importance of a healthcare institution that treats people with dignity while maintaining the highest standards of medical practice, Isaiah Washington set out to build more than just a hospital. He envisioned a trusted healthcare destination where individuals and families could find quality treatment, professional guidance, and reassurance at every stage of their healthcare journey.</p>
      <p>From that vision emerged Prime Care Health Solutions, an institution committed to delivering comprehensive healthcare services through a combination of experienced medical professionals, modern healthcare practices, and a culture centered on compassion and responsibility.</p>
      <blockquote class="pull">True healthcare is not simply about treating illnesses but also about protecting lives, promoting wellness, and helping communities build healthier futures.</blockquote>
      <p>Over time, Prime Care Health Solutions has embraced a broader approach to healthcare, extending its commitment beyond hospital-based treatment to include preventive care, pharmaceutical services, health education, patient support, and coordinated medical solutions.</p>
      <p>Today, Prime Care Health Solutions stands for the values upon which it was founded: integrity, excellence, compassion, innovation, and an unwavering commitment to human life. We continue to build on our founding vision, striving to make quality healthcare more accessible, dependable, and meaningful for every individual and family we serve.</p>
    </div>
  </div>
</section>

<section class="section section--tint">
  <div class="wrap mv">
    <article class="mv__card reveal">
      <span class="mv__icon"><?= icon('compass', 28) ?></span>
      <p class="eyebrow">Our mission</p>
      <h2>Why we exist</h2>
      <p>To provide exceptional, compassionate, and comprehensive healthcare services through medical excellence, innovation, and a patient-centered approach that improves lives and strengthens communities.</p>
    </article>
    <article class="mv__card mv__card--dark reveal" style="--d:100ms">
      <span class="mv__icon"><?= icon('star', 28) ?></span>
      <p class="eyebrow eyebrow--light">Our vision</p>
      <h2>Where we're going</h2>
      <p>To become a leading and trusted healthcare institution recognized for transforming healthcare delivery, advancing medical standards, and making quality healthcare accessible to individuals and communities.</p>
    </article>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Our core values</p>
      <h2>The principles behind every visit</h2>
    </div>
    <div class="values values--light">
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

<section class="section section--tint" id="leadership">
  <div class="wrap">
    <div class="section-head reveal">
      <p class="eyebrow">Medical Leadership &amp; Governance</p>
      <h2>Meet our executive clinical team</h2>
      <p class="lead">Experienced physicians, medical directors, and healthcare leaders shaping patient-centered medicine across every PrimeCare facility.</p>
    </div>
    <div class="doctor-cards">
      <?php foreach ($TEAM as $k => $doc): ?>
      <article class="doctor-card reveal">
        <div class="doctor-card__photo">
          <img src="<?= url($doc['image']) ?>" alt="<?= e($doc['name']) ?>">
          <span class="doctor-card__badge"><?= e($doc['badge']) ?></span>
        </div>
        <div class="doctor-card__body">
          <h3 class="doctor-card__name"><?= e($doc['name']) ?></h3>
          <p class="doctor-card__role"><?= e($doc['role']) ?></p>
          <span class="doctor-card__spec"><?= icon('shield', 14) ?> <?= e($doc['spec']) ?></span>
          <p class="doctor-card__desc"><?= e($doc['bio']) ?></p>
          <?php if (!empty($doc['link'])): ?>
          <a href="<?= url($doc['link']) ?>" class="card__link" style="margin-top:16px">Read founder profile <?= icon('arrow', 16) ?></a>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--dark promise">
  <div class="wrap promise__inner reveal">
    <p class="eyebrow eyebrow--light">Our promise</p>
    <h2>Healthcare is more than a profession; it is a responsibility and a commitment to humanity.</h2>
    <div class="promise__lines">
      <span>Every patient matters.</span>
      <span>Every life is valuable.</span>
      <span>Every opportunity to make a difference counts.</span>
    </div>
    <p class="promise__tag"><?= e(SITE_NAME) ?> — <?= e(SITE_TAGLINE) ?></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
