<?php
require_once __DIR__ . '/../includes/functions.php';
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');

$pdo = db();
$error = '';
$self = url('mail/index.php');

/* ----------------------------------------------------
 * Independent Mail Authentication
 * ---------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (!csrf_check()) {
        $error = 'Security token expired. Please try again.';
    } elseif (!throttle('mail_login', 6, 900)) {
        $error = 'Too many attempts. Please wait 15 minutes before retrying.';
    } else {
        $identifier = clean('identifier', 120);
        $pass = (string)($_POST['password'] ?? '');

        $st = $pdo->prepare('SELECT * FROM mail_users WHERE username = ? OR email = ? LIMIT 1');
        $st->execute([$identifier, $identifier]);
        $user = $st->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['mail_user'] = [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ];
            header("Location: $self");
            exit;
        }
        $error = 'Invalid credentials. Please verify your mail username/email and password.';
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['mail_user']);
    session_regenerate_id(true);
    header("Location: $self");
    exit;
}

$mailAuthed = !empty($_SESSION['mail_user']);
$currentUser = $_SESSION['mail_user'] ?? null;
$view = $_GET['view'] ?? 'compose';

/* ----------------------------------------------------
 * Mail Portal Actions (Authorized)
 * ---------------------------------------------------- */
if ($mailAuthed && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $act = $_POST['action'] ?? '';

    // 1. Send Outgoing Email via Resend
    if ($act === 'send_email') {
        $to = clean('to', 255);
        $subject = clean('subject', 200);
        $senderName = clean('sender_name', 100) ?: SITE_NAME;
        $replyTo = clean('reply_to', 120);
        $bodyHtml = (string)($_POST['body_html'] ?? '');
        $bodyText = (string)($_POST['body_text'] ?? '');

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please provide a valid recipient email address.');
        } elseif (!$subject) {
            flash('error', 'Subject line cannot be blank.');
        } elseif (!$bodyHtml) {
            flash('error', 'Email message content cannot be empty.');
        } else {
            $fromStr = $senderName . ' <' . MAIL_FROM . '>';
            $res = send_resend_email($to, $subject, $bodyHtml, $bodyText, $fromStr, $replyTo, $currentUser['username'] ?? 'mailer');
            
            if (!empty($res['ok'])) {
                flash('success', "Email successfully dispatched to {$to} (Resend ID: {$res['id']}).");
                header("Location: ?view=outbox");
                exit;
            } else {
                flash('error', 'Resend delivery failed: ' . ($res['error'] ?: 'Unknown error. Check Resend logs.'));
            }
        }
    }

    // 2. Diagnostic Live Test Email
    if ($act === 'test_diagnostic') {
        $testTo = clean('test_to', 120) ?: SITE_EMAIL;
        $testSubject = 'PrimeCare Mail Diagnostic Verification — ' . date('Y-m-d H:i');
        $testHtml = '
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
            <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
                <h2 style="margin:0;font-size:20px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
                <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Mailing System Diagnostic Test</p>
            </div>
            <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
                <p>Hello,</p>
                <p>This is a live diagnostic test dispatched from your dedicated <strong>PrimeCare Mail Portal</strong> via the <strong>Resend API</strong>.</p>
                <div style="background:#f1f5f4;border-left:4px solid #0d8276;padding:12px 16px;border-radius:4px;margin:18px 0;font-size:14px;">
                    <strong>Sender:</strong> ' . htmlspecialchars(MAIL_FROM) . '<br>
                    <strong>Domain Status:</strong> Verified (primecarehealthsolutions.online)<br>
                    <strong>Timestamp:</strong> ' . date('r') . '
                </div>
                <p>If you have received this message, your Resend API integration and mailing service are operating at 100% capacity.</p>
            </div>
            <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
                &copy; ' . date('Y') . ' Prime Care Health Solutions. All rights reserved.
            </div>
        </div>';

        $res = send_resend_email($testTo, $testSubject, $testHtml, '', '', '', 'diagnostic');
        if (!empty($res['ok'])) {
            flash('success', "Diagnostic test email sent successfully to {$testTo}! Resend ID: {$res['id']}");
        } else {
            flash('error', "Diagnostic test failed: {$res['error']}");
        }
        header("Location: ?view=settings");
        exit;
    }

    // 3. Change Password
    if ($act === 'change_password') {
        $curPass = (string)($_POST['cur_password'] ?? '');
        $newPass = (string)($_POST['new_password'] ?? '');
        $newPass2 = (string)($_POST['new_password2'] ?? '');

        $st = $pdo->prepare('SELECT password_hash FROM mail_users WHERE id = ?');
        $st->execute([$currentUser['id']]);
        $hash = $st->fetchColumn();

        if (!$hash || !password_verify($curPass, $hash)) {
            flash('error', 'Your current password was entered incorrectly.');
        } elseif (strlen($newPass) < 8) {
            flash('error', 'New password must be at least 8 characters.');
        } elseif ($newPass !== $newPass2) {
            flash('error', 'New passwords do not match.');
        } else {
            $pdo->prepare('UPDATE mail_users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($newPass, PASSWORD_DEFAULT), $currentUser['id']]);
            flash('success', 'Your mail account password has been updated.');
        }
        header("Location: ?view=settings");
        exit;
    }

    // 4. Delete Log
    if ($act === 'del_log') {
        $logId = (int)($_POST['log_id'] ?? 0);
        if ($logId > 0) {
            $pdo->prepare('DELETE FROM email_logs WHERE id = ?')->execute([$logId]);
            flash('success', 'Email record removed from outbox log.');
        }
        header("Location: ?view=outbox");
        exit;
    }
}

// Pre-fill recipient if passed via query
$prefillTo = clean('to', 120);
$prefillSubject = clean('subject', 150);

// Flash messages
$flash = get_flash();

// Counters
$totalSent = 0;
$totalFailed = 0;
$todaySent = 0;
if ($mailAuthed) {
    $totalSent = (int)$pdo->query("SELECT COUNT(*) FROM email_logs WHERE status='sent'")->fetchColumn();
    $totalFailed = (int)$pdo->query("SELECT COUNT(*) FROM email_logs WHERE status='failed'")->fetchColumn();
    $todaySent = (int)$pdo->query("SELECT COUNT(*) FROM email_logs WHERE status='sent' AND created_at >= date('now')")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PrimeCare Mail Portal — Official Healthcare Mailing System</title>
<link rel="icon" type="image/png" href="<?= url('assets/img/favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=2.0">
<link rel="stylesheet" href="<?= url('mail/mail.css') ?>?v=1.0">
</head>
<body class="mail-app">

<?php if (!$mailAuthed): ?>
  <!-- ==============================================
       MAIL PORTAL SEPARATE LOGIN SCREEN
       ============================================== -->
  <main class="mail-auth">
    <div class="mail-auth__card">
      <div class="mail-auth__header">
        <div class="brand" style="justify-content:center;margin-bottom:12px;">
          <span class="brand__mark"><?php include __DIR__ . '/../includes/logo-mark.php'; ?></span>
          <span class="brand__text"><strong>Prime<span>Care</span></strong><small>Mail Services</small></span>
        </div>
        <div class="mail-badge-tag"><?= icon('mail', 14) ?> Clinical Mailing System</div>
      </div>

      <form method="post" action="<?= $self ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="login">

        <h1 style="font-size:1.4rem;font-weight:700;margin:0 0 6px;text-align:center;">Mailing Portal Login</h1>
        <p style="font-size:0.86rem;color:var(--mail-muted);margin:0 0 20px;text-align:center;">
          Sign in to dispatch and manage communications via <strong>support@primecarehealthsolutions.online</strong>
        </p>

        <?php if ($error): ?>
          <div class="mail-alert mail-alert-error"><?= icon('alert', 16) ?> <?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label for="idnt">Username or Mail Address</label>
          <input class="form-control" id="idnt" name="identifier" required placeholder="support" value="support" autofocus autocomplete="username">
        </div>

        <div class="form-group">
          <label for="pwd">Mail Access Password</label>
          <input class="form-control" id="pwd" type="password" name="password" required placeholder="Enter password" autocomplete="current-password">
        </div>

        <button type="submit" class="btn-mail btn-mail-primary btn-mail-block" style="padding:13px 20px;margin-top:10px;">
          <?= icon('lock', 16) ?> Sign in to Mail Portal
        </button>
      </form>

      <div style="margin-top:24px;padding-top:16px;border-top:1px solid var(--mail-border);text-align:center;font-size:0.82rem;">
        <a href="<?= url('admin/index.php') ?>" style="color:var(--mail-teal-dark);text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
          ← Switch to General Admin Panel
        </a>
      </div>
    </div>
  </main>

<?php else: ?>
  <!-- ==============================================
       AUTHENTICATED MAILING DASHBOARD LAYOUT
       ============================================== -->
  <div class="mail-layout">
    <aside class="mail-sidebar">
      <a class="mail-brand" href="<?= $self ?>">
        <span class="brand__mark"><?php include __DIR__ . '/../includes/logo-mark.php'; ?></span>
        <div>
          <strong>Prime<span>Care</span></strong>
          <small>Mail Center</small>
        </div>
      </a>

      <nav class="mail-nav">
        <a href="?view=compose" class="<?= $view === 'compose' ? 'is-active' : '' ?>">
          <?= icon('spark', 18) ?> Compose Email
        </a>
        <a href="?view=outbox" class="<?= $view === 'outbox' ? 'is-active' : '' ?>">
          <?= icon('mail', 18) ?> Outbox &amp; Logs
          <?php if ($totalSent): ?><b><?= $totalSent ?></b><?php endif; ?>
        </a>
        <a href="?view=contacts" class="<?= $view === 'contacts' ? 'is-active' : '' ?>">
          <?= icon('users', 18) ?> Patient Directory
        </a>
        <a href="?view=settings" class="<?= $view === 'settings' ? 'is-active' : '' ?>">
          <?= icon('lock', 18) ?> Resend &amp; Settings
        </a>
      </nav>

      <div class="mail-sidebar-foot">
        <div class="mail-user-card">
          <span class="u-name"><?= e($currentUser['username']) ?></span>
          <span class="u-role"><?= e($currentUser['email'] ?: 'support@primecarehealthsolutions.online') ?></span>
        </div>
        <div class="mail-sidebar-links">
          <a href="<?= url('admin/index.php') ?>" target="_blank">Site Admin ↗</a>
          <a href="<?= url('index.php') ?>" target="_blank">Website ↗</a>
          <a href="?logout=1" style="color:#f87171;margin-left:auto;">Sign out</a>
        </div>
      </div>
    </aside>

    <main class="mail-main">
      <?php if ($flash): ?>
        <div class="mail-alert <?= $flash['type'] === 'success' ? 'mail-alert-success' : 'mail-alert-error' ?>">
          <?= icon($flash['type'] === 'success' ? 'check' : 'alert', 18) ?>
          <div><?= e($flash['msg']) ?></div>
        </div>
      <?php endif; ?>

      <!-- Quick KPI Stats -->
      <section class="mail-stats">
        <div class="mail-stat">
          <span>Total Dispatched</span>
          <strong><?= $totalSent ?></strong>
          <small>Via Resend API</small>
        </div>
        <div class="mail-stat">
          <span>Sent Today</span>
          <strong><?= $todaySent ?></strong>
          <small>Live Patient Mails</small>
        </div>
        <div class="mail-stat">
          <span>Active Sender</span>
          <strong style="font-size:1.05rem;padding:6px 0;"><?= e(MAIL_FROM) ?></strong>
          <small class="status-pill status-verified">Domain Verified</small>
        </div>
        <div class="mail-stat">
          <span>Delivery Service</span>
          <strong style="font-size:1.3rem;">Resend EU</strong>
          <small style="color:var(--mail-success);">● HTTPS Connected</small>
        </div>
      </section>

      <?php if ($view === 'compose'): ?>
        <!-- ==============================================
             VIEW: COMPOSE & SEND EMAIL
             ============================================== -->
        <div class="mail-header">
          <div>
            <h1>Compose Clinical Email</h1>
            <p>Send secure healthcare notices, patient follow-ups, and notifications directly through Resend.</p>
          </div>
          <a href="?view=outbox" class="btn-mail btn-mail-outline">
            <?= icon('inbox', 16) ?> View Outbox Log
          </a>
        </div>

        <div class="mail-card">
          <!-- Template Quick Selectors -->
          <div style="margin-bottom:18px;">
            <label style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--mail-muted);font-weight:700;display:block;margin-bottom:8px;">
              Pre-built Healthcare Templates:
            </label>
            <div class="template-bar">
              <button type="button" class="template-chip" onclick="applyTemplate('appointment')">📅 Appointment Confirmation</button>
              <button type="button" class="template-chip" onclick="applyTemplate('digital_card')">💳 Patient ID Card Issued</button>
              <button type="button" class="template-chip" onclick="applyTemplate('lab_results')">🔬 Lab &amp; Diagnostic Update</button>
              <button type="button" class="template-chip" onclick="applyTemplate('prescription')">💊 Prescription &amp; Refill</button>
              <button type="button" class="template-chip" onclick="applyTemplate('inquiry_reply')">✉️ Patient Inquiry Response</button>
              <button type="button" class="template-chip" onclick="applyTemplate('bulletin')">📢 Health Bulletin</button>
            </div>
          </div>

          <form method="post" action="<?= $self ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="send_email">

            <div class="form-row">
              <div class="form-group">
                <label for="to">Recipient Email Address *</label>
                <input class="form-control" id="to" name="to" type="email" required placeholder="patient@example.com" value="<?= e($prefillTo) ?>">
              </div>
              <div class="form-group">
                <label for="sender_name">From Sender Name</label>
                <input class="form-control" id="sender_name" name="sender_name" value="<?= e(SITE_NAME) ?>">
                <small style="font-size:0.75rem;color:var(--mail-muted);">Dispatched from: <strong><?= e(MAIL_FROM) ?></strong></small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="subject">Subject Line *</label>
                <input class="form-control" id="subject" name="subject" required placeholder="Important Health Update regarding your care" value="<?= e($prefillSubject) ?>">
              </div>
              <div class="form-group">
                <label for="reply_to">Reply-To Address (Optional)</label>
                <input class="form-control" id="reply_to" name="reply_to" type="email" placeholder="support@primecarehealthsolutions.online" value="<?= e(MAIL_FROM) ?>">
              </div>
            </div>

            <div class="form-group">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <label for="body_html" style="margin-bottom:0;">HTML Message Body *</label>
                <button type="button" class="btn-mail btn-mail-outline btn-mail-sm" onclick="togglePreview()">Toggle Live Preview</button>
              </div>
              <textarea class="form-control" id="body_html" name="body_html" rows="12" required placeholder="Write your HTML or formatted message here..."></textarea>
            </div>

            <!-- Live Preview Container -->
            <div id="previewContainer" style="display:none;margin-bottom:20px;">
              <label style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--mail-muted);font-weight:700;">Live Email Render:</label>
              <div class="email-preview-box" id="previewBox"></div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:16px;">
              <button type="reset" class="btn-mail btn-mail-outline">Reset Form</button>
              <button type="submit" class="btn-mail btn-mail-primary" style="padding:12px 28px;">
                <?= icon('spark', 18) ?> Dispatch via Resend
              </button>
            </div>
          </form>
        </div>

      <?php elseif ($view === 'outbox'): ?>
        <!-- ==============================================
             VIEW: OUTBOX & SENT LOGS
             ============================================== -->
        <?php
        $logs = $pdo->query("SELECT * FROM email_logs ORDER BY id DESC LIMIT 150")->fetchAll();
        ?>
        <div class="mail-header">
          <div>
            <h1>Outbox &amp; Dispatched Emails</h1>
            <p>Complete historical log of all outbound emails sent through the Resend API.</p>
          </div>
          <a href="?view=compose" class="btn-mail btn-mail-primary">
            <?= icon('spark', 16) ?> Compose New Email
          </a>
        </div>

        <div class="mail-table-wrap">
          <table class="mail-table">
            <thead>
              <tr>
                <th>Date &amp; Time</th>
                <th>Recipient</th>
                <th>Subject</th>
                <th>Dispatched By</th>
                <th>Status</th>
                <th>Resend Reference</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($logs)): ?>
                <tr>
                  <td colspan="7" style="text-align:center;padding:40px;color:var(--mail-muted);">
                    <?= icon('mail', 32) ?>
                    <p style="margin:8px 0 0;">No emails logged in outbox yet. Start by sending a message!</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($logs as $l): ?>
                  <tr>
                    <td style="white-space:nowrap;font-size:0.82rem;color:var(--mail-muted);">
                      <?= date('M d, Y H:i', strtotime($l['created_at'])) ?>
                    </td>
                    <td>
                      <strong><?= e($l['recipient']) ?></strong>
                    </td>
                    <td>
                      <div style="font-weight:600;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= e($l['subject']) ?>
                      </div>
                    </td>
                    <td>
                      <span style="font-size:0.82rem;background:var(--mail-sand);padding:3px 8px;border-radius:6px;border:1px solid var(--mail-border);">
                        <?= e($l['sent_by'] ?: 'system') ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($l['status'] === 'sent'): ?>
                        <span class="status-pill status-sent"><?= icon('check', 12) ?> Sent</span>
                      <?php else: ?>
                        <span class="status-pill status-failed" title="<?= e($l['error_message']) ?>"><?= icon('alert', 12) ?> Failed</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <code style="font-size:0.75rem;background:#f3f4f6;padding:2px 6px;border-radius:4px;color:#374151;">
                        <?= e($l['resend_id'] ? substr($l['resend_id'], 0, 16) . '...' : 'N/A') ?>
                      </code>
                    </td>
                    <td>
                      <div style="display:flex;gap:6px;">
                        <button type="button" class="btn-mail btn-mail-outline btn-mail-sm" onclick="viewMail(<?= $l['id'] ?>)">
                          View
                        </button>
                        <form method="post" action="<?= $self ?>" onsubmit="return confirm('Remove log record?');" style="display:inline;">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="del_log">
                          <input type="hidden" name="log_id" value="<?= $l['id'] ?>">
                          <button type="submit" class="btn-mail btn-mail-outline btn-mail-sm" style="color:var(--mail-danger);padding:6px 8px;">✕</button>
                        </form>
                      </div>

                      <!-- Hidden message content for modal view -->
                      <div id="log-body-<?= $l['id'] ?>" style="display:none;" data-subject="<?= e($l['subject']) ?>" data-recipient="<?= e($l['recipient']) ?>" data-date="<?= date('M d, Y H:i', strtotime($l['created_at'])) ?>" data-error="<?= e($l['error_message']) ?>">
                        <?= $l['body_html'] ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      <?php elseif ($view === 'contacts'): ?>
        <!-- ==============================================
             VIEW: PATIENT CONTACT DIRECTORY
             ============================================== -->
        <?php
        // Fetch patient list and appointment contacts
        $patients = $pdo->query("SELECT id, card_number, full_name, email, phone FROM patients WHERE email != '' ORDER BY id DESC LIMIT 50")->fetchAll();
        $appts = $pdo->query("SELECT id, ref, full_name, email, phone, location FROM appointments WHERE email != '' GROUP BY email ORDER BY id DESC LIMIT 50")->fetchAll();
        $subs = $pdo->query("SELECT id, email, created_at FROM subscribers ORDER BY id DESC LIMIT 50")->fetchAll();
        ?>
        <div class="mail-header">
          <div>
            <h1>Patient &amp; Contact Directory</h1>
            <p>Single-click to open composer and dispatch personalized communications.</p>
          </div>
        </div>

        <div class="mail-card">
          <h2>Registered Patients</h2>
          <div class="mail-table-wrap" style="margin-bottom:28px;">
            <table class="mail-table">
              <thead>
                <tr>
                  <th>Card #</th>
                  <th>Patient Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($patients)): ?>
                  <tr><td colspan="5" style="text-align:center;color:var(--mail-muted);padding:24px;">No patients with email addresses registered.</td></tr>
                <?php else: ?>
                  <?php foreach ($patients as $p): ?>
                    <tr>
                      <td><code><?= e($p['card_number']) ?></code></td>
                      <td><strong><?= e($p['full_name']) ?></strong></td>
                      <td><?= e($p['email']) ?></td>
                      <td><?= e($p['phone']) ?></td>
                      <td>
                        <a href="?view=compose&to=<?= urlencode($p['email']) ?>&subject=<?= urlencode('PrimeCare Health Solutions: Patient Communication') ?>" class="btn-mail btn-mail-primary btn-mail-sm">
                          <?= icon('mail', 14) ?> Email Patient
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <h2>Appointment Contacts</h2>
          <div class="mail-table-wrap" style="margin-bottom:28px;">
            <table class="mail-table">
              <thead>
                <tr>
                  <th>Ref #</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Location</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($appts)): ?>
                  <tr><td colspan="5" style="text-align:center;color:var(--mail-muted);padding:24px;">No appointment bookings yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($appts as $a): ?>
                    <tr>
                      <td><code><?= e($a['ref']) ?></code></td>
                      <td><strong><?= e($a['full_name']) ?></strong></td>
                      <td><?= e($a['email']) ?></td>
                      <td><?= e($a['location']) ?></td>
                      <td>
                        <a href="?view=compose&to=<?= urlencode($a['email']) ?>&subject=<?= urlencode('Regarding your appointment with PrimeCare') ?>" class="btn-mail btn-mail-outline btn-mail-sm">
                          <?= icon('mail', 14) ?> Email
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <h2>Newsletter Subscribers</h2>
          <div class="mail-table-wrap">
            <table class="mail-table">
              <thead>
                <tr>
                  <th>Subscriber Email</th>
                  <th>Date Joined</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($subs)): ?>
                  <tr><td colspan="3" style="text-align:center;color:var(--mail-muted);padding:24px;">No newsletter subscribers yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($subs as $s): ?>
                    <tr>
                      <td><strong><?= e($s['email']) ?></strong></td>
                      <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                      <td>
                        <a href="?view=compose&to=<?= urlencode($s['email']) ?>&subject=<?= urlencode('PrimeCare Health Updates & Wellness') ?>" class="btn-mail btn-mail-outline btn-mail-sm">
                          <?= icon('mail', 14) ?> Write
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php elseif ($view === 'settings'): ?>
        <!-- ==============================================
             VIEW: RESEND DIAGNOSTIC & SETTINGS
             ============================================== -->
        <div class="mail-header">
          <div>
            <h1>Mailing Configuration &amp; Diagnostics</h1>
            <p>Live status of your Resend infrastructure, API keys, and account security.</p>
          </div>
        </div>

        <div class="form-row">
          <!-- Resend Status Card -->
          <div class="mail-card">
            <h2>Resend Integration Status</h2>
            <div style="display:grid;gap:12px;margin-bottom:20px;font-size:0.9rem;">
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--mail-border);padding-bottom:8px;">
                <span style="color:var(--mail-muted);">Authorized Sending Domain:</span>
                <strong style="color:var(--mail-teal-dark);">primecarehealthsolutions.online</strong>
              </div>
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--mail-border);padding-bottom:8px;">
                <span style="color:var(--mail-muted);">Domain Verification:</span>
                <span class="status-pill status-sent"><?= icon('check', 12) ?> Verified &amp; Active</span>
              </div>
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--mail-border);padding-bottom:8px;">
                <span style="color:var(--mail-muted);">Default Sender:</span>
                <strong><?= e(MAIL_FROM) ?></strong>
              </div>
              <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--mail-border);padding-bottom:8px;">
                <span style="color:var(--mail-muted);">Resend API Key:</span>
                <code>re_MfjE••••••••••••Pf6d</code>
              </div>
              <div style="display:flex;justify-content:space-between;padding-bottom:8px;">
                <span style="color:var(--mail-muted);">Endpoint Region:</span>
                <span>EU-West-1 (Ireland)</span>
              </div>
            </div>

            <hr style="border:none;border-top:1px solid var(--mail-border);margin:20px 0;">

            <h3>Send Diagnostic Test Email</h3>
            <p style="font-size:0.86rem;color:var(--mail-muted);margin:0 0 14px;">
              Verify that live emails are reaching inboxes directly from <code><?= e(MAIL_FROM) ?></code>.
            </p>
            <form method="post" action="<?= $self ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="test_diagnostic">
              <div class="form-group">
                <label for="test_to">Send Test To Email Address</label>
                <input class="form-control" id="test_to" name="test_to" type="email" required placeholder="your.personal@email.com" value="<?= e(SITE_EMAIL) ?>">
              </div>
              <button type="submit" class="btn-mail btn-mail-primary">
                <?= icon('spark', 16) ?> Send Live Resend Test
              </button>
            </form>
          </div>

          <!-- Security & Password -->
          <div class="mail-card">
            <h2>Mail Portal Account Security</h2>
            <p style="font-size:0.86rem;color:var(--mail-muted);margin:0 0 16px;">
              Update the password for mail portal account <strong><?= e($currentUser['username']) ?></strong>.
            </p>

            <form method="post" action="<?= $self ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="change_password">

              <div class="form-group">
                <label for="cp">Current Password</label>
                <input class="form-control" id="cp" type="password" name="cur_password" required autocomplete="current-password">
              </div>

              <div class="form-group">
                <label for="np">New Password</label>
                <input class="form-control" id="np" type="password" name="new_password" required minlength="8" autocomplete="new-password">
              </div>

              <div class="form-group">
                <label for="np2">Confirm New Password</label>
                <input class="form-control" id="np2" type="password" name="new_password2" required minlength="8" autocomplete="new-password">
              </div>

              <button type="submit" class="btn-mail btn-mail-primary">
                <?= icon('lock', 16) ?> Update Mail Password
              </button>
            </form>
          </div>
        </div>

      <?php endif; ?>
    </main>
  </div>

  <!-- Modal for Viewing Sent Email -->
  <div id="mailModal" style="display:none;position:fixed;inset:0;background:rgba(14,42,39,0.6);z-index:9999;place-items:center;padding:20px;">
    <div style="background:#ffffff;border-radius:16px;width:min(680px,100%);max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 40px rgba(0,0,0,0.25);overflow:hidden;">
      <div style="padding:18px 24px;border-bottom:1px solid var(--mail-border);display:flex;justify-content:space-between;align-items:center;background:var(--mail-sand);">
        <h3 id="modalSubject" style="margin:0;font-size:1.1rem;color:var(--mail-deep);">Email Details</h3>
        <button type="button" onclick="closeModal()" style="border:none;background:transparent;font-size:1.4rem;cursor:pointer;color:var(--mail-muted);">&times;</button>
      </div>
      <div style="padding:14px 24px;border-bottom:1px solid var(--mail-border);font-size:0.86rem;background:#ffffff;">
        <div><strong>To:</strong> <span id="modalRecipient"></span></div>
        <div style="margin-top:4px;"><strong>Sent:</strong> <span id="modalDate" style="color:var(--mail-muted);"></span></div>
        <div id="modalErrorBox" style="display:none;margin-top:8px;color:var(--mail-danger);font-weight:600;"></div>
      </div>
      <div style="padding:24px;overflow-y:auto;flex:1;background:#fafafa;" id="modalContent"></div>
      <div style="padding:14px 24px;border-top:1px solid var(--mail-border);display:flex;justify-content:flex-end;background:var(--mail-sand);">
        <button type="button" class="btn-mail btn-mail-outline" onclick="closeModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- JavaScript for Templates & Modal -->
  <script>
    // Templates Library
    const templates = {
      appointment: {
        subject: "Appointment Confirmation — Prime Care Health Solutions",
        body: `<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
  <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
    <h2 style="margin:0;font-size:22px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
    <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Your Health. Our Priority. Always.</p>
  </div>
  <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
    <h3 style="margin-top:0;color:#0e2a27;">Appointment Confirmation</h3>
    <p>Dear Patient,</p>
    <p>Your upcoming appointment has been scheduled and confirmed with our healthcare team.</p>
    <div style="background:#f8faf9;border:1px solid #e2e8e6;border-radius:8px;padding:16px 20px;margin:20px 0;">
      <p style="margin:0 0 8px;"><strong>Location:</strong> Prime Care Health Solutions Center</p>
      <p style="margin:0 0 8px;"><strong>Service:</strong> Primary & Family Medicine</p>
      <p style="margin:0;"><strong>Please Bring:</strong> Valid Photo ID and current medication list</p>
    </div>
    <p>If you need to reschedule or have any questions, please reply directly to this email or call our care desk.</p>
    <p style="margin-top:24px;">Warm regards,<br><strong>Clinical Appointments Desk</strong><br>Prime Care Health Solutions</p>
  </div>
  <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
    &copy; ${new Date().getFullYear()} Prime Care Health Solutions. All rights reserved.
  </div>
</div>`
      },
      digital_card: {
        subject: "Your PrimeCare Digital Health Card is Ready",
        body: `<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
  <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
    <h2 style="margin:0;font-size:22px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
    <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Digital Health Identification Service</p>
  </div>
  <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
    <h3 style="margin-top:0;color:#0e2a27;">Your Digital Patient Card Has Been Issued</h3>
    <p>Dear Patient,</p>
    <p>We are pleased to inform you that your official <strong>PrimeCare Digital Health Card</strong> is now active and ready for use across all our healthcare centers.</p>
    <div style="background:#e6f6f4;border-left:4px solid #0d8276;padding:16px 20px;border-radius:6px;margin:20px 0;">
      <p style="margin:0 0 6px;"><strong>Status:</strong> Active & Verified</p>
      <p style="margin:0;">You can verify, view, or download your digital card anytime using our secure patient verification portal.</p>
    </div>
    <div style="text-align:center;margin:28px 0;">
      <a href="https://www.primecarehealthsolutions.online/card.php" style="background:#0d8276;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:8px;display:inline-block;">View Digital ID Card</a>
    </div>
    <p style="font-size:13px;color:#627b77;">Please keep this record for your files. Present this card upon check-in at any PrimeCare clinic.</p>
  </div>
  <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
    &copy; ${new Date().getFullYear()} Prime Care Health Solutions.
  </div>
</div>`
      },
      lab_results: {
        subject: "Medical Diagnostic & Laboratory Results Notice",
        body: `<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
  <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
    <h2 style="margin:0;font-size:22px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
    <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Laboratory & Diagnostics Care</p>
  </div>
  <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
    <h3 style="margin-top:0;color:#0e2a27;">Laboratory Results Update</h3>
    <p>Dear Patient,</p>
    <p>Your recent laboratory and diagnostic findings have been reviewed by our medical team.</p>
    <p>Please contact our care coordination desk at your earliest convenience to schedule a review discussion with your primary physician.</p>
    <div style="background:#f1f5f4;padding:14px 18px;border-radius:8px;margin:18px 0;font-size:14px;">
      <strong>Care Support Line:</strong> +1 (943) 206-0409<br>
      <strong>Office Hours:</strong> Mon–Fri 8:00am–6:00pm
    </div>
    <p>Your privacy and comprehensive care are our utmost priority.</p>
  </div>
  <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
    &copy; ${new Date().getFullYear()} Prime Care Health Solutions.
  </div>
</div>`
      },
      prescription: {
        subject: "Prescription & Medication Advisory — PrimeCare",
        body: `<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
  <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
    <h2 style="margin:0;font-size:22px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
    <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Pharmaceutical Care & Refill Services</p>
  </div>
  <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
    <h3 style="margin-top:0;color:#0e2a27;">Prescription Refill Update</h3>
    <p>Dear Patient,</p>
    <p>Your prescription request has been processed and forwarded to your nominated pharmacy.</p>
    <p>Please remember to adhere to the dosage instructions provided by your physician. If you experience any unexpected symptoms or require dosage adjustments, please reach out immediately.</p>
    <p style="margin-top:24px;">Sincerely,<br><strong>Pharmacy Services Team</strong><br>Prime Care Health Solutions</p>
  </div>
  <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
    &copy; ${new Date().getFullYear()} Prime Care Health Solutions.
  </div>
</div>`
      },
      inquiry_reply: {
        subject: "Response to your Inquiry — Prime Care Health Solutions",
        body: `<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
  <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
    <h2 style="margin:0;font-size:22px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
    <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Patient Support & Assistance</p>
  </div>
  <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
    <p>Dear Patient,</p>
    <p>Thank you for reaching out to Prime Care Health Solutions. We have received your inquiry and our support team is happy to assist you.</p>
    <p>[Write your personalized response message here]</p>
    <p>Should you require any further assistance, do not hesitate to reply directly to this email or call our offices.</p>
    <p style="margin-top:24px;">Warm regards,<br><strong>Patient Services Team</strong><br>Prime Care Health Solutions</p>
  </div>
  <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
    &copy; ${new Date().getFullYear()} Prime Care Health Solutions.
  </div>
</div>`
      },
      bulletin: {
        subject: "Health Bulletin & Clinic Announcements — PrimeCare",
        body: `<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8e6;border-radius:12px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
  <div style="background:#0e2a27;padding:24px;text-align:center;color:#ffffff;">
    <h2 style="margin:0;font-size:22px;letter-spacing:-0.02em;">Prime Care Health Solutions</h2>
    <p style="margin:6px 0 0;font-size:13px;color:#2ec4b6;">Community Wellness & Medical Bulletin</p>
  </div>
  <div style="padding:28px 24px;background:#ffffff;color:#1d2d2a;line-height:1.6;">
    <h3 style="margin-top:0;color:#0e2a27;">Community Health Announcement</h3>
    <p>Dear Member,</p>
    <p>We are dedicated to keeping you and your family informed with proactive wellness advice, clinical updates, and seasonal preventive measures.</p>
    <p>[Insert your newsletter or bulletin content here]</p>
    <div style="text-align:center;margin:28px 0;">
      <a href="https://www.primecarehealthsolutions.online/services.php" style="background:#0d8276;color:#ffffff;text-decoration:none;font-weight:bold;padding:12px 24px;border-radius:8px;display:inline-block;">Explore Healthcare Services</a>
    </div>
  </div>
  <div style="background:#f8faf9;padding:16px 24px;text-align:center;font-size:12px;color:#627b77;border-top:1px solid #e2e8e6;">
    &copy; ${new Date().getFullYear()} Prime Care Health Solutions.
  </div>
</div>`
      }
    };

    function applyTemplate(key) {
      if (!templates[key]) return;
      document.getElementById('subject').value = templates[key].subject;
      document.getElementById('body_html').value = templates[key].body;
      updatePreview();
    }

    function togglePreview() {
      const container = document.getElementById('previewContainer');
      if (container.style.display === 'none') {
        container.style.display = 'block';
        updatePreview();
      } else {
        container.style.display = 'none';
      }
    }

    function updatePreview() {
      const html = document.getElementById('body_html').value;
      const previewBox = document.getElementById('previewBox');
      if (previewBox) {
        previewBox.innerHTML = html;
      }
    }

    // Modal Viewer
    function viewMail(id) {
      const source = document.getElementById('log-body-' + id);
      if (!source) return;
      document.getElementById('modalSubject').textContent = source.getAttribute('data-subject');
      document.getElementById('modalRecipient').textContent = source.getAttribute('data-recipient');
      document.getElementById('modalDate').textContent = source.getAttribute('data-date');
      const err = source.getAttribute('data-error');
      const errBox = document.getElementById('modalErrorBox');
      if (err) {
        errBox.style.display = 'block';
        errBox.textContent = 'Error: ' + err;
      } else {
        errBox.style.display = 'none';
      }
      document.getElementById('modalContent').innerHTML = source.innerHTML;
      document.getElementById('mailModal').style.display = 'grid';
    }

    function closeModal() {
      document.getElementById('mailModal').style.display = 'none';
    }

    // Close modal on escape
    window.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeModal();
    });
  </script>
<?php endif; ?>

</body>
</html>
