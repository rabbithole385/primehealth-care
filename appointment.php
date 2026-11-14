<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$old = [
    'service'  => $_GET['service'] ?? '',
    'location' => $_GET['location'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'service'        => clean('service', 40),
        'location'       => clean('location', 40),
        'patient_type'   => clean('patient_type', 20),
        'preferred_date' => clean('preferred_date', 10),
        'preferred_time' => clean('preferred_time', 20),
        'full_name'      => clean('full_name', 120),
        'email'          => clean('email', 160),
        'phone'          => clean('phone', 40),
        'contact_method' => clean('contact_method', 20),
        'notes'          => clean('notes', 600),
        'consent'        => !empty($_POST['consent']),
    ];
    if (!csrf_check()) $errors['form'] = 'Your session expired. Please try again.';
    if (!empty($_POST['website'])) $errors['form'] = 'Submission blocked.'; // honeypot
    if (!isset($SERVICES[$old['service']]))   $errors['service'] = 'Please choose a service.';
    if (!isset($LOCATIONS[$old['location']])) $errors['location'] = 'Please choose a location.';
    if (!in_array($old['patient_type'], ['new', 'returning'], true)) $errors['patient_type'] = 'Please tell us if you are a new or returning patient.';
    $d = DateTime::createFromFormat('Y-m-d', $old['preferred_date']);
    if (!$d || $d->format('Y-m-d') !== $old['preferred_date']) $errors['preferred_date'] = 'Please choose a preferred date.';
    elseif ($d < new DateTime('today')) $errors['preferred_date'] = 'Please choose a date from today onward.';
    elseif ($d->format('N') == 7) $errors['preferred_date'] = 'Our clinics are closed on Sundays. Please choose another day.';
    if (!in_array($old['preferred_time'], ['morning', 'afternoon', 'any'], true)) $errors['preferred_time'] = 'Please choose a preferred time.';
    if (mb_strlen($old['full_name']) < 2) $errors['full_name'] = 'Please enter your full name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';
    if (strlen(preg_replace('/\D/', '', $old['phone'])) < 10) $errors['phone'] = 'Please enter a valid phone number.';
    if (!in_array($old['contact_method'], ['phone', 'email', 'text'], true)) $old['contact_method'] = 'phone';
    if (!$old['consent']) $errors['consent'] = 'Please confirm so we can contact you.';
    if (!$errors && !throttle('appt', 4)) $errors['form'] = 'Too many requests. Please call us or try again in a few minutes.';

    if (!$errors) {
        $ref = 'PC-' . strtoupper(substr($old['location'], 0, 3)) . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $st = db()->prepare('INSERT INTO appointments (ref, full_name, email, phone, location, service, patient_type, preferred_date, preferred_time, contact_method, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$ref, $old['full_name'], $old['email'], $old['phone'], $old['location'], $old['service'], $old['patient_type'], $old['preferred_date'], $old['preferred_time'], $old['contact_method'], $old['notes']]);
        notify("New appointment request {$ref}",
            "Reference: {$ref}\nName: {$old['full_name']}\nEmail: {$old['email']}\nPhone: {$old['phone']}\nLocation: {$LOCATIONS[$old['location']]['name']}\nService: {$SERVICES[$old['service']]['title']}\nPatient: {$old['patient_type']}\nPreferred: {$old['preferred_date']} ({$old['preferred_time']})\nContact via: {$old['contact_method']}\nNotes: {$old['notes']}\n",
            $old['email']);
        $_SESSION['last_appt'] = ['ref' => $ref, 'name' => $old['full_name'], 'location' => $old['location'], 'service' => $old['service'], 'date' => $old['preferred_date'], 'time' => $old['preferred_time'], 'method' => $old['contact_method']];
        header('Location: ' . url('appointment.php?done=1'));
        exit;
    }
}

$active = 'appointment';
$page_title = 'Book an Appointment';
$page_desc = 'Request an appointment at Prime Care Health Solutions in Atlanta, Miami, Los Angeles or Houston. Quick, simple and confirmed by our patient support team.';
require __DIR__ . '/includes/header.php';

$done = isset($_GET['done']) ? ($_SESSION['last_appt'] ?? null) : null;
$times = ['morning' => 'Morning (8am–12pm)', 'afternoon' => 'Afternoon (12pm–6pm)', 'any' => 'Any time'];
$err = fn($k) => isset($errors[$k]) ? '<p class="field__err" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '';
$inv = fn($k) => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<section class="page-hero page-hero--compact">
  <div class="hero__bg" aria-hidden="true"><span class="blob blob--1"></span><span class="grid-dots"></span></div>
  <div class="wrap page-hero__inner">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">Book Appointment</span></nav>
    <p class="eyebrow">Book an appointment</p>
    <h1><?= $done ? 'Request <em>received</em>' : 'Let’s get you <em>seen</em>' ?></h1>
  </div>
</section>

<section class="section section--flush-top">
  <div class="wrap appt">
<?php if ($done): $L = $LOCATIONS[$done['location']]; ?>
    <div class="success reveal">
      <div class="success__check" aria-hidden="true"><svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24" fill="none"/><path fill="none" d="m15 27 7 7 15-15"/></svg></div>
      <h2>Thank you, <?= e(strtok($done['name'], ' ')) ?>.</h2>
      <p>Your appointment request has been received. Our patient support team will contact you by <strong><?= e($done['method']) ?></strong> to confirm your visit.</p>
      <dl class="summary">
        <div><dt>Reference</dt><dd class="mono"><?= e($done['ref']) ?></dd></div>
        <div><dt>Service</dt><dd><?= e($SERVICES[$done['service']]['title']) ?></dd></div>
        <div><dt>Location</dt><dd><?= e($L['name']) ?></dd></div>
        <div><dt>Preferred</dt><dd><?= e(date('l, F j, Y', strtotime($done['date']))) ?> · <?= e($times[$done['time']]) ?></dd></div>
      </dl>
      <p class="muted">Need to change something or speak to someone now? Call <a href="tel:<?= e($L['tel']) ?>"><?= e($L['phone']) ?></a>.</p>
      <div class="success__actions">
        <a class="btn btn--primary" href="<?= url('index.php') ?>">Back to home</a>
        <button class="btn btn--outline" type="button" onclick="window.print()">Print summary</button>
      </div>
    </div>
<?php else: ?>
    <form class="wizard" method="post" action="<?= url('appointment.php') ?>" novalidate data-wizard>
      <?= csrf_field() ?>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

      <ol class="wizard__progress" aria-label="Form progress">
        <li class="is-active" data-step-ind="1"><span>1</span> Your visit</li>
        <li data-step-ind="2"><span>2</span> Date &amp; time</li>
        <li data-step-ind="3"><span>3</span> Your details</li>
      </ol>

      <?php if (isset($errors['form'])): ?><div class="alert alert--error" role="alert"><?= icon('alert', 18) ?> <?= e($errors['form']) ?></div><?php endif; ?>
      <?php if ($errors && !isset($errors['form'])): ?><div class="alert alert--error" role="alert"><?= icon('alert', 18) ?> Please check the highlighted fields below.</div><?php endif; ?>

      <fieldset class="wizard__step is-active" data-step="1">
        <legend>What can we help you with?</legend>
        <div class="field">
          <span class="field__label" id="lbl-service">Service <b aria-hidden="true">*</b></span>
          <div class="choice-grid" role="radiogroup" aria-labelledby="lbl-service">
            <?php foreach ($SERVICES as $k => $s): ?>
            <label class="choice">
              <input type="radio" name="service" value="<?= e($k) ?>" required<?= $old['service'] === $k ? ' checked' : '' ?>>
              <span class="choice__box"><?= icon($s['icon'], 22) ?> <?= e($s['title']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <?= $err('service') ?>
        </div>
        <div class="field">
          <span class="field__label" id="lbl-location">Location <b aria-hidden="true">*</b></span>
          <div class="choice-grid choice-grid--4" role="radiogroup" aria-labelledby="lbl-location">
            <?php foreach ($LOCATIONS as $k => $l): ?>
            <label class="choice">
              <input type="radio" name="location" value="<?= e($k) ?>" required<?= $old['location'] === $k ? ' checked' : '' ?>>
              <span class="choice__box choice__box--stack"><strong><?= e($l['short']) ?></strong><small><?= e($l['state']) ?></small></span>
            </label>
            <?php endforeach; ?>
          </div>
          <?= $err('location') ?>
        </div>
        <div class="field">
          <span class="field__label" id="lbl-pt">Are you a new or returning patient? <b aria-hidden="true">*</b></span>
          <div class="seg" role="radiogroup" aria-labelledby="lbl-pt">
            <label><input type="radio" name="patient_type" value="new" required<?= ($old['patient_type'] ?? '') === 'new' ? ' checked' : '' ?>><span>New patient</span></label>
            <label><input type="radio" name="patient_type" value="returning"<?= ($old['patient_type'] ?? '') === 'returning' ? ' checked' : '' ?>><span>Returning patient</span></label>
          </div>
          <?= $err('patient_type') ?>
        </div>
        <div class="wizard__nav"><span></span><button class="btn btn--primary" type="button" data-next>Continue <?= icon('arrow', 18) ?></button></div>
      </fieldset>

      <fieldset class="wizard__step" data-step="2">
        <legend>When works best for you?</legend>
        <div class="field">
          <label class="field__label" for="preferred_date">Preferred date <b aria-hidden="true">*</b></label>
          <input class="input" type="date" id="preferred_date" name="preferred_date" required min="<?= date('Y-m-d') ?>" value="<?= e($old['preferred_date'] ?? '') ?>"<?= $inv('preferred_date') ?>>
          <p class="field__hint">We're open Monday to Saturday. We'll confirm the exact time with you.</p>
          <?= $err('preferred_date') ?>
        </div>
        <div class="field">
          <span class="field__label" id="lbl-time">Preferred time <b aria-hidden="true">*</b></span>
          <div class="seg seg--3" role="radiogroup" aria-labelledby="lbl-time">
            <?php foreach ($times as $k => $t): ?>
            <label><input type="radio" name="preferred_time" value="<?= e($k) ?>" required<?= ($old['preferred_time'] ?? '') === $k ? ' checked' : '' ?>><span><?= e($t) ?></span></label>
            <?php endforeach; ?>
          </div>
          <?= $err('preferred_time') ?>
        </div>
        <div class="wizard__nav"><button class="btn btn--text" type="button" data-prev>← Back</button><button class="btn btn--primary" type="button" data-next>Continue <?= icon('arrow', 18) ?></button></div>
      </fieldset>

      <fieldset class="wizard__step" data-step="3">
        <legend>How can we reach you?</legend>
        <div class="row-2">
          <div class="field">
            <label class="field__label" for="full_name">Full name <b aria-hidden="true">*</b></label>
            <input class="input" id="full_name" name="full_name" required autocomplete="name" value="<?= e($old['full_name'] ?? '') ?>"<?= $inv('full_name') ?>>
            <?= $err('full_name') ?>
          </div>
          <div class="field">
            <label class="field__label" for="phone">Phone number <b aria-hidden="true">*</b></label>
            <input class="input" id="phone" name="phone" type="tel" required autocomplete="tel" placeholder="(555) 555-5555" value="<?= e($old['phone'] ?? '') ?>"<?= $inv('phone') ?>>
            <?= $err('phone') ?>
          </div>
        </div>
        <div class="field">
          <label class="field__label" for="email">Email address <b aria-hidden="true">*</b></label>
          <input class="input" id="email" name="email" type="email" required autocomplete="email" value="<?= e($old['email'] ?? '') ?>"<?= $inv('email') ?>>
          <?= $err('email') ?>
        </div>
        <div class="field">
          <span class="field__label" id="lbl-cm">Best way to confirm with you</span>
          <div class="seg seg--3" role="radiogroup" aria-labelledby="lbl-cm">
            <?php foreach (['phone' => 'Phone call', 'text' => 'Text message', 'email' => 'Email'] as $k => $t): ?>
            <label><input type="radio" name="contact_method" value="<?= $k ?>"<?= ($old['contact_method'] ?? 'phone') === $k ? ' checked' : '' ?>><span><?= $t ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="field">
          <label class="field__label" for="notes">Anything we should know? <span class="opt">(optional)</span></label>
          <textarea class="input" id="notes" name="notes" rows="3" maxlength="600" placeholder="E.g. accessibility needs, preferred language, or a short reason for your visit."><?= e($old['notes'] ?? '') ?></textarea>
          <p class="field__hint"><?= icon('lock', 14) ?> Please don't include detailed medical history here — we'll discuss that with you privately.</p>
        </div>
        <label class="check">
          <input type="checkbox" name="consent" value="1" required<?= !empty($old['consent']) ? ' checked' : '' ?>>
          <span>I agree to be contacted by <?= e(SITE_NAME) ?> about this appointment request.</span>
        </label>
        <?= $err('consent') ?>
        <div class="wizard__nav"><button class="btn btn--text" type="button" data-prev>← Back</button><button class="btn btn--primary btn--lg" type="submit"><?= icon('check', 18) ?> Request Appointment</button></div>
      </fieldset>
    </form>

    <aside class="appt__side">
      <div class="side-card side-card--accent">
        <h3><?= icon('phone', 20) ?> Prefer to call?</h3>
        <ul class="phone-list">
          <?php foreach ($LOCATIONS as $l): ?>
          <li><span><?= e($l['short']) ?></span><a href="tel:<?= e($l['tel']) ?>"><?= e($l['phone']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="side-card">
        <h3><?= icon('clock', 20) ?> Clinic hours</h3>
        <p>Monday – Friday: 8:00am – 6:00pm<br>Saturday: 9:00am – 2:00pm<br>Sunday: Closed</p>
      </div>
      <div class="side-card side-card--warn">
        <h3><?= icon('alert', 20) ?> Emergency?</h3>
        <p>Don't wait for an appointment. Call <a href="tel:911"><strong>911</strong></a> or go to the nearest emergency room.</p>
      </div>
    </aside>
<?php endif; ?>
  </div>
</section>
<?php if ($errors): ?>
<script>window.__wizardStart = <?= (isset($errors['service']) || isset($errors['location']) || isset($errors['patient_type'])) ? 1 : ((isset($errors['preferred_date']) || isset($errors['preferred_time'])) ? 2 : 3) ?>;</script>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
