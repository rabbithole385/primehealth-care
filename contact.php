<?php
require_once __DIR__ . '/includes/functions.php';
$errors = [];
$old = ['location' => $_GET['location'] ?? ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'full_name' => clean('full_name', 120),
        'email'     => clean('email', 160),
        'phone'     => clean('phone', 40),
        'location'  => clean('location', 40),
        'subject'   => clean('subject', 60),
        'message'   => clean('message', 2000),
    ];
    if (!csrf_check()) $errors['form'] = 'Your session expired. Please try again.';
    if (!empty($_POST['website'])) $errors['form'] = 'Submission blocked.';
    if (mb_strlen($old['full_name']) < 2) $errors['full_name'] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (mb_strlen($old['message']) < 10) $errors['message'] = 'Please write a short message (at least 10 characters).';
    if ($old['location'] && !isset($LOCATIONS[$old['location']])) $old['location'] = '';
    if (!$errors && !throttle('contact', 5)) $errors['form'] = 'Too many messages. Please try again in a few minutes.';
    if (!$errors) {
        db()->prepare('INSERT INTO messages (full_name, email, phone, location, subject, message) VALUES (?,?,?,?,?,?)')
            ->execute([$old['full_name'], $old['email'], $old['phone'], $old['location'], $old['subject'], $old['message']]);
        notify('New website message: ' . ($old['subject'] ?: 'General enquiry'),
            "From: {$old['full_name']} <{$old['email']}>\nPhone: {$old['phone']}\nLocation: {$old['location']}\nSubject: {$old['subject']}\n\n{$old['message']}\n", $old['email']);
        flash('success', 'Thank you — your message has been sent. Our team will get back to you shortly.');
        header('Location: ' . url('contact.php?sent=1#form'));
        exit;
    }
}

$active = 'contact';
$page_title = 'Contact Us';
$page_desc = 'Contact Prime Care Health Solutions — call our Atlanta, Miami, Los Angeles or Houston locations, or send us a message online.';
require __DIR__ . '/includes/header.php';
$hero = ['eyebrow' => 'Contact us', 'title' => 'We’re here to <em>help</em>', 'lead' => 'Questions about services, appointments or billing? Reach the location nearest you or send us a message — our patient support team will respond promptly.', 'crumb' => 'Contact'];
require __DIR__ . '/includes/page-hero.php';
$flash = get_flash();
$err = fn($k) => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '';
$inv = fn($k) => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$subjects = ['General enquiry', 'Appointments', 'Pharmacy & prescriptions', 'Billing & insurance', 'Feedback', 'Careers', 'Other'];
?>
<section class="section section--flush-top">
  <div class="wrap">
    <div class="quick-contact">
      <?php foreach ($LOCATIONS as $k => $l): ?>
      <a class="qc reveal" href="tel:<?= e($l['tel']) ?>">
        <span class="qc__icon"><?= icon('phone', 20) ?></span>
        <span class="qc__body"><small><?= e($l['short']) ?>, <?= e($l['state']) ?></small><strong><?= e($l['phone']) ?></strong></span>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="contact" id="form">
      <form class="form-card reveal" method="post" action="<?= url('contact.php') ?>#form" novalidate>
        <h2>Send us a message</h2>
        <p class="muted">Fields marked * are required. We usually respond within one business day.</p>
        <?php if ($flash): ?><div class="alert alert--success" role="status"><?= icon('check', 18) ?> <?= e($flash['msg']) ?></div><?php endif; ?>
        <?php if (isset($errors['form'])): ?><div class="alert alert--error" role="alert"><?= icon('alert', 18) ?> <?= e($errors['form']) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="row-2">
          <div class="field">
            <label class="field__label" for="c-name">Full name <b aria-hidden="true">*</b></label>
            <input class="input" id="c-name" name="full_name" required autocomplete="name" value="<?= e($old['full_name'] ?? '') ?>"<?= $inv('full_name') ?>>
            <?= $err('full_name') ?>
          </div>
          <div class="field">
            <label class="field__label" for="c-email">Email <b aria-hidden="true">*</b></label>
            <input class="input" id="c-email" name="email" type="email" required autocomplete="email" value="<?= e($old['email'] ?? '') ?>"<?= $inv('email') ?>>
            <?= $err('email') ?>
          </div>
        </div>
        <div class="row-2">
          <div class="field">
            <label class="field__label" for="c-phone">Phone <span class="opt">(optional)</span></label>
            <input class="input" id="c-phone" name="phone" type="tel" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>">
          </div>
          <div class="field">
            <label class="field__label" for="c-loc">Location <span class="opt">(optional)</span></label>
            <select class="input" id="c-loc" name="location">
              <option value="">Any / not sure</option>
              <?php foreach ($LOCATIONS as $k => $l): ?><option value="<?= e($k) ?>"<?= ($old['location'] ?? '') === $k ? ' selected' : '' ?>><?= e($l['short']) ?>, <?= e($l['state']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label class="field__label" for="c-subject">Topic</label>
          <select class="input" id="c-subject" name="subject">
            <?php foreach ($subjects as $s): ?><option<?= ($old['subject'] ?? '') === $s ? ' selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label class="field__label" for="c-msg">Message <b aria-hidden="true">*</b></label>
          <textarea class="input" id="c-msg" name="message" rows="5" required maxlength="2000" data-counter<?= $inv('message') ?>><?= e($old['message'] ?? '') ?></textarea>
          <p class="field__hint field__hint--row"><span><?= icon('lock', 14) ?> Please avoid sharing detailed medical information.</span><span data-count-out>0 / 2000</span></p>
          <?= $err('message') ?>
        </div>
        <button class="btn btn--primary btn--lg" type="submit"><?= icon('mail', 18) ?> Send Message</button>
      </form>

      <aside class="contact__side reveal">
        <div class="side-card side-card--dark">
          <h3>Head office</h3>
          <p class="loc-line"><?= icon('pin', 20) ?> <span><?= e($LOCATIONS['atlanta']['street']) ?><br><?= e($LOCATIONS['atlanta']['city']) ?><br>United States</span></p>
          <p class="loc-line"><?= icon('phone', 20) ?> <a href="tel:<?= e($LOCATIONS['atlanta']['tel']) ?>"><?= e($LOCATIONS['atlanta']['phone']) ?></a></p>
          <p class="loc-line"><?= icon('mail', 20) ?> <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a></p>
        </div>
        <div class="side-card">
          <h3><?= icon('calendar', 20) ?> Need an appointment?</h3>
          <p>Use our quick online request — it takes about two minutes.</p>
          <a class="btn btn--primary btn--sm" href="<?= url('appointment.php') ?>">Book now</a>
        </div>
        <div class="side-card side-card--warn">
          <h3><?= icon('alert', 20) ?> Emergency?</h3>
          <p>Please don't use this form. Call <a href="tel:911"><strong>911</strong></a> immediately.</p>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
