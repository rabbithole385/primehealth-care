<?php
$active = 'card';
$page_title = 'Digital Health Card & Verification — Prime Care Health Solutions';
$page_desc = 'Verify your PrimeCare Health Solutions digital patient membership card.';
require __DIR__ . '/includes/header.php';
$hero = ['eyebrow' => 'Patient Services', 'title' => 'Digital Health Card<br><em>Verification Portal</em>', 'lead' => 'Verify membership status, check health card details, or view your digital patient identity card.', 'crumb' => 'Card Verification'];
require __DIR__ . '/includes/page-hero.php';

$pdo = db();
$card_num = strtoupper(trim($_GET['id'] ?? $_GET['card'] ?? ''));
$patient = null;
$searched = false;

if ($card_num !== '') {
    $searched = true;
    $st = $pdo->prepare('SELECT * FROM patients WHERE UPPER(card_number) = ? LIMIT 1');
    $st->execute([$card_num]);
    $patient = $st->fetch();
}
?>

<section class="section">
  <div class="wrap" style="max-width:880px">
    <div style="background:#fff;border:1px solid var(--line);border-radius:var(--r-xl);padding:32px;box-shadow:var(--sh-1);margin-bottom:36px">
      <form action="<?= url('card.php') ?>" method="get" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
        <div style="flex:1;min-width:240px">
          <label for="card-id" style="display:block;font-weight:700;margin-bottom:8px;font-size:.9rem;color:var(--ink)">Enter Patient Card ID Number</label>
          <input id="card-id" name="id" type="text" value="<?= e($card_num) ?>" placeholder="e.g. PC-2026-1042" required style="width:100%;padding:14px 16px;border:1px solid var(--line);border-radius:12px;font:inherit;text-transform:uppercase">
        </div>
        <button type="submit" class="btn btn--primary btn--lg" style="height:52px"><?= icon('check', 18) ?> Verify Card</button>
      </form>
    </div>

    <?php if ($searched && $patient): ?>
      <div class="patient-card-wrap reveal" style="margin-top:20px">
        <!-- Physical Card Mockup -->
        <div class="pcard" id="printable-card" style="background:linear-gradient(135deg, #061b4f 0%, #0a3d82 60%, #0c56b3 100%);color:#fff;border-radius:24px;padding:36px;box-shadow:0 24px 60px rgba(6,27,79,0.35);position:relative;overflow:hidden;border:1px solid rgba(255,255,255,0.15)">
          <!-- Decorative BG Pattern -->
          <div style="position:absolute;right:-40px;top:-40px;width:240px;height:240px;border-radius:50%;background:radial-gradient(circle,rgba(0,196,245,0.2) 0%,transparent 70%);pointer-events:none"></div>
          
          <!-- Card Header -->
          <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid rgba(255,255,255,0.15);padding-bottom:20px;margin-bottom:24px">
            <div style="display:flex;align-items:center;gap:14px">
              <div style="background:#fff;border-radius:12px;padding:8px;width:48px;height:48px;display:grid;place-items:center">
                <img src="<?= url('assets/img/logo-icon.png') ?>" alt="PrimeCare" style="width:100%;height:100%;object-fit:contain">
              </div>
              <div>
                <strong style="display:block;font-size:1.15rem;letter-spacing:-.01em"><?= e(SITE_NAME) ?></strong>
                <small style="color:#8fe3ff;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em">Official Medical Identification Card</small>
              </div>
            </div>
            <span style="background:rgba(46,204,113,0.2);color:#2ecc71;border:1px solid rgba(46,204,113,0.4);padding:5px 14px;border-radius:999px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;display:inline-flex;align-items:center;gap:6px">
              ● <?= e(strtoupper($patient['status'])) ?>
            </span>
          </div>

          <!-- Card Body -->
          <div style="display:grid;grid-template-columns:130px 1fr 1fr;gap:28px;align-items:center">
            <!-- Avatar / Photo placeholder -->
            <div style="width:130px;aspect-ratio:1;border-radius:18px;background:rgba(255,255,255,0.1);border:2px solid rgba(255,255,255,0.25);display:grid;place-items:center;color:#fff;font-size:2.8rem;font-weight:800;font-family:var(--f-display)">
              <?= strtoupper(substr($patient['full_name'], 0, 1)) ?>
            </div>

            <!-- Details Col 1 -->
            <div>
              <span style="color:#8fe3ff;font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:2px">Patient Name</span>
              <strong style="font-size:1.35rem;display:block;margin-bottom:14px;color:#fff"><?= e($patient['full_name']) ?></strong>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                  <span style="color:#8fe3ff;font-size:.72rem;text-transform:uppercase;display:block">Card ID</span>
                  <strong style="font-family:monospace;font-size:1rem;color:#ffbe53"><?= e($patient['card_number']) ?></strong>
                </div>
                <div>
                  <span style="color:#8fe3ff;font-size:.72rem;text-transform:uppercase;display:block">Blood Group</span>
                  <strong style="font-size:1rem"><?= e($patient['blood_group'] ?: 'N/A') ?></strong>
                </div>
              </div>
            </div>

            <!-- Details Col 2 -->
            <div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div>
                  <span style="color:#8fe3ff;font-size:.72rem;text-transform:uppercase;display:block">Date of Birth</span>
                  <span style="font-size:.9rem;color:#fff"><?= e($patient['dob'] ?: 'N/A') ?></span>
                </div>
                <div>
                  <span style="color:#8fe3ff;font-size:.72rem;text-transform:uppercase;display:block">Gender</span>
                  <span style="font-size:.9rem;color:#fff"><?= e($patient['gender'] ?: 'N/A') ?></span>
                </div>
              </div>
              <div>
                <span style="color:#8fe3ff;font-size:.72rem;text-transform:uppercase;display:block">Emergency Contact</span>
                <span style="font-size:.88rem;color:#fff"><?= e($patient['emergency_contact'] ?: 'N/A') ?></span>
              </div>
            </div>
          </div>

          <!-- Card Footer -->
          <div style="border-top:1px solid rgba(255,255,255,0.15);margin-top:24px;padding-top:18px;display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#9fb2d8">
            <div>
              <span>Issued: <strong><?= e($patient['issue_date'] ?: date('Y-m-d', strtotime($patient['created_at']))) ?></strong></span>
              <span style="margin-left:16px">Expires: <strong><?= e($patient['expiry_date'] ?: 'Annual Renewal') ?></strong></span>
            </div>
            <span style="letter-spacing:.02em">Authorized PrimeCare Medical Network · 24/7 Support</span>
          </div>
        </div>

        <div style="display:flex;justify-content:center;gap:14px;margin-top:24px">
          <button onclick="window.print()" class="btn btn--primary"><?= icon('download', 18) ?> Print / Save Card</button>
          <a href="<?= url('appointment.php') ?>" class="btn btn--outline"><?= icon('calendar', 18) ?> Book Appointment with this Card</a>
        </div>
      </div>
    <?php elseif ($searched): ?>
      <div style="background:#fff;border:1px solid var(--line);border-radius:var(--r-xl);padding:36px;text-align:center">
        <div style="width:64px;height:64px;border-radius:50%;background:#fee2e2;color:#ef4444;display:grid;place-items:center;margin:0 auto 16px">
          <?= icon('alert', 32) ?>
        </div>
        <h3 style="margin-bottom:8px">Card ID Not Found</h3>
        <p style="color:var(--muted);max-width:440px;margin:0 auto 20px">No patient record matches card ID "<?= e($card_num) ?>". Please verify the number or reach out to our patient care desk.</p>
        <a href="<?= url('contact.php') ?>" class="btn btn--outline">Contact Patient Support</a>
      </div>
    <?php else: ?>
      <div style="background:#fff;border:1px solid var(--line);border-radius:var(--r-xl);padding:36px;text-align:center">
        <span style="font-size:2.8rem;display:block;margin-bottom:12px">💳</span>
        <h3 style="margin-bottom:8px">PrimeCare Digital Health Card Service</h3>
        <p style="color:var(--muted);max-width:520px;margin:0 auto 24px">Every registered PrimeCare patient receives an official Medical ID Card connected to their health records, emergency contacts, and priority care appointments.</p>
        <p style="font-size:.85rem;color:var(--ink-2)">New patient? <a href="<?= url('appointment.php') ?>" style="font-weight:700;color:var(--teal)">Schedule your first appointment</a> to have your health card issued.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
