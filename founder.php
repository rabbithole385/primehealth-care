<?php
$active = 'founder';
$page_title = 'Meet Our Founder — Isaiah Washington';
$page_desc = 'Isaiah Washington, Founder & Visionary Leader of Prime Care Health Solutions. A life dedicated to healing, a legacy built on compassion.';
require __DIR__ . '/includes/header.php';
$hero = ['eyebrow' => 'Meet our founder', 'title' => 'A Life Dedicated to Healing.<br><em>A Legacy Built on Compassion.</em>', 'lead' => '', 'crumb' => 'Our Founder'];
require __DIR__ . '/includes/page-hero.php';
?>
<section class="section founder">
  <div class="wrap founder__grid">
    <aside class="founder__card reveal">
      <div class="founder__portrait" aria-hidden="true">
        <!-- Replace this monogram with a real photo: <img src="assets/img/founder.jpg" alt="Isaiah Washington"> -->
        <span>IW</span>
      </div>
      <h2>Isaiah Washington</h2>
      <p class="founder__role">Founder &amp; Visionary Leader<br>Prime Care Health Solutions</p>
      <ul class="founder__tags">
        <li>Medical professional</li><li>Healthcare advocate</li><li>Visionary leader</li>
      </ul>
    </aside>

    <div class="prose reveal">
      <p class="dropcap">Behind every great institution is a vision, and behind Prime Care Health Solutions is the vision of Isaiah Washington, a respected medical professional, healthcare advocate, and visionary leader whose commitment to improving lives has shaped his journey and professional legacy.</p>
      <p>Isaiah Washington’s journey into healthcare was driven by more than professional ambition. It was founded on a deeply rooted belief that access to quality healthcare should never be considered a privilege, but an essential part of human dignity. From an early stage in his life, he developed a profound appreciation for the value of human life, the importance of compassion, and the difference that dedicated medical professionals can make in the lives of individuals and families.</p>
      <p>His path was not defined by the desire to simply become a medical professional, but by the determination to become someone capable of making a lasting difference. Through years of learning, professional development, and dedication to the medical field, Washington cultivated a philosophy of healthcare that places people before procedures and compassion alongside clinical excellence.</p>

      <h3 class="prose__h">A defining realization</h3>
      <p>Throughout his professional journey, he came to recognize that the healthcare industry needed more than medical expertise alone. It needed leadership, accountability, innovation, and institutions that genuinely understood the needs of the people they served. This realization became a defining moment in his career and ultimately inspired him to establish Prime Care Health Solutions.</p>
      <p>With a vision to create a healthcare institution founded on trust, integrity, and exceptional patient care, Washington set out to build an organization that would bring together medical services, pharmaceutical solutions, preventive healthcare, and patient support under one unified commitment to wellness.</p>

      <blockquote class="pull">The true measure of healthcare is not only in the lives we treat, but in the lives we improve.</blockquote>

      <p>Today, Isaiah Washington continues to represent the values upon which Prime Care Health Solutions was envisioned. His commitment to responsible healthcare leadership, continuous improvement, and compassionate service remains central to the institution’s identity and its ambitions for the future.</p>
      <p>Beyond the title of founder, he sees his work as a responsibility to contribute to something greater than himself: a healthcare legacy that future generations can trust, build upon, and benefit from.</p>
    </div>
  </div>
</section>

<section class="section section--dark">
  <div class="wrap philosophy reveal">
    <span class="philosophy__q"><?= icon('quote', 44) ?></span>
    <p class="eyebrow eyebrow--light">His philosophy</p>
    <blockquote>“Healthcare is not simply about treating an illness; it is about understanding a human being, restoring hope, preserving dignity, and making a meaningful difference in someone’s life. Every patient deserves to be heard, respected, and cared for with the highest level of compassion and professionalism.”</blockquote>
    <p class="philosophy__by">— Isaiah Washington</p>
  </div>
</section>

<section class="section">
  <div class="wrap legacy reveal">
    <div>
      <p class="eyebrow">A legacy in progress</p>
      <h2>The beginning of a greater mission</h2>
    </div>
    <div>
      <p>For Isaiah Washington, founding Prime Care Health Solutions was never intended to be the final destination. It was the beginning of a greater mission: to contribute to the advancement of healthcare, empower communities through better health services, and build an institution where excellence and compassion will always go hand in hand.</p>
      <ul class="legacy__list">
        <li><span><?= icon('spark', 20) ?></span> Advance healthcare</li>
        <li><span><?= icon('users', 20) ?></span> Empower communities</li>
        <li><span><?= icon('heart', 20) ?></span> Unite excellence &amp; compassion</li>
      </ul>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
