<?php
require_once __DIR__ . '/../includes/functions.php';
header('X-Frame-Options: DENY');
header('X-Robots-Tag: noindex, nofollow');

$pdo = db();
$hasAdmin = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0;
$error = '';
$self = url('admin/index.php');

/* ---------- First-run setup ---------- */
if (!$hasAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'setup') {
    $u = clean('username', 40); $p = (string)($_POST['password'] ?? ''); $p2 = (string)($_POST['password2'] ?? '');
    if (!csrf_check()) $error = 'Session expired, try again.';
    elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $u)) $error = 'Username: 3–40 letters, numbers, . _ -';
    elseif (strlen($p) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($p !== $p2) $error = 'Passwords do not match.';
    else {
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['admin'] = $u;
        header("Location: $self"); exit;
    }
}

/* ---------- Login / logout ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (!csrf_check()) $error = 'Session expired, try again.';
    elseif (!throttle('login', 6, 900)) $error = 'Too many attempts. Wait 15 minutes.';
    else {
        $st = $pdo->prepare('SELECT * FROM admins WHERE username = ?'); $st->execute([clean('username', 40)]);
        $a = $st->fetch();
        if ($a && password_verify((string)($_POST['password'] ?? ''), $a['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $a['username'];
            header("Location: $self"); exit;
        }
        $error = 'Invalid username or password.';
    }
}
if (isset($_GET['logout'])) { unset($_SESSION['admin']); session_regenerate_id(true); header("Location: $self"); exit; }

$authed = !empty($_SESSION['admin']);

/* ---------- Actions ---------- */
if ($authed && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $act = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    $statuses = ['new', 'contacted', 'confirmed', 'completed', 'cancelled'];
    if ($act === 'status' && in_array($_POST['status'] ?? '', $statuses, true)) $pdo->prepare('UPDATE appointments SET status=? WHERE id=?')->execute([$_POST['status'], $id]);
    if ($act === 'del_appt') $pdo->prepare('DELETE FROM appointments WHERE id=?')->execute([$id]);
    if ($act === 'read') $pdo->prepare('UPDATE messages SET is_read=1 WHERE id=?')->execute([$id]);
    if ($act === 'del_msg') $pdo->prepare('DELETE FROM messages WHERE id=?')->execute([$id]);
    if ($act === 'del_sub') $pdo->prepare('DELETE FROM subscribers WHERE id=?')->execute([$id]);
    if ($act === 'add_patient') {
        $cnum = clean('card_number', 40);
        if (!$cnum) {
            $cnum = 'PC-' . date('Y') . '-' . str_pad((string)rand(1001, 9999), 4, '0', STR_PAD_LEFT);
        }
        $name = clean('full_name', 100);
        $dob  = clean('dob', 30);
        $gen  = clean('gender', 20);
        $bg   = clean('blood_group', 10);
        $ph   = clean('phone', 40);
        $em   = clean('email', 100);
        $ec   = clean('emergency_contact', 100);
        $addr = clean('address', 200);
        if ($name !== '') {
            $pdo->prepare('INSERT INTO patients (card_number, full_name, dob, gender, blood_group, phone, email, emergency_contact, address, issue_date, expiry_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$cnum, $name, $dob, $gen, $bg, $ph, $em, $ec, $addr, date('Y-m-d'), date('Y-m-d', strtotime('+1 year')), 'active']);
            flash('success', "Patient card $cnum successfully issued for $name.");
        }
    }
    if ($act === 'del_patient') $pdo->prepare('DELETE FROM patients WHERE id=?')->execute([$id]);
    if ($act === 'testmail') {
        $ok = notify('Test email from ' . SITE_NAME . ' admin', "This is a test notification sent from the admin panel at " . date('r') . ".\nIf you can read this, email notifications are working.");
        flash($ok ? 'success' : 'error', $ok ? 'Test email sent to ' . SITE_EMAIL . '. Check the inbox (and spam).' : 'Test email failed (driver: ' . MAIL_DRIVER . '). Check your email variables and the deploy logs.');
    }
    if ($act === 'password') {
        $p = (string)($_POST['password'] ?? '');
        if (strlen($p) >= 8 && $p === ($_POST['password2'] ?? '')) {
            $pdo->prepare('UPDATE admins SET password_hash=? WHERE username=?')->execute([password_hash($p, PASSWORD_DEFAULT), $_SESSION['admin']]);
            flash('success', 'Password updated.');
        } else flash('error', 'Password must be 8+ characters and match.');
    }
    header('Location: ' . $self . '?' . http_build_query(array_intersect_key($_GET, array_flip(['view', 'status', 'loc', 'q'])))); exit;
}

/* ---------- CSV export ---------- */
if ($authed && isset($_GET['export'])) {
    $t = $_GET['export'] === 'messages' ? 'messages' : ($_GET['export'] === 'subscribers' ? 'subscribers' : 'appointments');
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=primecare-{$t}-" . date('Ymd') . '.csv');
    $out = fopen('php://output', 'w'); $rows = $pdo->query("SELECT * FROM $t ORDER BY id DESC")->fetchAll();
    if ($rows) fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $r) fputcsv($out, array_map(fn($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'" . $v : $v, $r));
    exit;
}

$view = $_GET['view'] ?? 'appointments';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin · <?= e(SITE_NAME) ?></title>
<link rel="icon" href="<?= url('assets/img/favicon.png') ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= url('assets/img/apple-touch-icon.png') ?>">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=1.0">
<link rel="stylesheet" href="<?= url('admin/admin.css') ?>?v=1.0">
</head>
<body class="admin">
<?php if (!$authed): ?>
  <main class="auth">
    <form class="auth__card" method="post">
      <div class="brand"><span class="brand__mark"><?php include __DIR__ . '/../includes/logo-mark.php'; ?></span><span class="brand__text"><strong>Prime<span>Care</span></strong><small>Admin Panel</small></span></div>
      <?= csrf_field() ?>
      <?php if (!$hasAdmin): ?>
        <h1>Create admin account</h1>
        <p class="muted">First-time setup. This screen disappears once an admin exists.</p>
        <input type="hidden" name="action" value="setup">
        <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
        <div class="field"><label class="field__label" for="u">Username</label><input class="input" id="u" name="username" required autocomplete="username"></div>
        <div class="field"><label class="field__label" for="p">Password</label><input class="input" id="p" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
        <div class="field"><label class="field__label" for="p2">Confirm password</label><input class="input" id="p2" type="password" name="password2" required minlength="8" autocomplete="new-password"></div>
        <button class="btn btn--primary btn--block btn--lg">Create account</button>
      <?php else: ?>
        <h1>Welcome back</h1>
        <p class="muted">Sign in to manage appointments and messages.</p>
        <input type="hidden" name="action" value="login">
        <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
        <div class="field"><label class="field__label" for="u">Username</label><input class="input" id="u" name="username" required autocomplete="username" autofocus></div>
        <div class="field"><label class="field__label" for="p">Password</label><input class="input" id="p" type="password" name="password" required autocomplete="current-password"></div>
        <button class="btn btn--primary btn--block btn--lg"><?= icon('lock', 18) ?> Sign in</button>
      <?php endif; ?>
      <a class="auth__back" href="<?= url('index.php') ?>">← Back to website</a>
    </form>
  </main>
<?php else:
  $counts = [
      'new'    => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status='new'")->fetchColumn(),
      'all'    => (int)$pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn(),
      'unread' => (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read=0')->fetchColumn(),
      'subs'   => (int)$pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn(),
      'patients' => (int)$pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn(),
      'week'   => (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE created_at >= datetime('now','-7 days')")->fetchColumn(),
  ];
  $flash = get_flash();
  $badge = ['new' => 'b-new', 'contacted' => 'b-contacted', 'confirmed' => 'b-confirmed', 'completed' => 'b-completed', 'cancelled' => 'b-cancelled'];
?>
  <div class="adm">
    <aside class="adm__side">
      <a class="brand brand--light" href="<?= $self ?>"><span class="brand__mark"><?php include __DIR__ . '/../includes/logo-mark.php'; ?></span><span class="brand__text"><strong>Prime<span>Care</span></strong><small>Admin</small></span></a>
      <nav>
        <a href="?view=appointments" class="<?= $view === 'appointments' ? 'is-on' : '' ?>"><?= icon('calendar', 18) ?> Appointments <?php if ($counts['new']): ?><b><?= $counts['new'] ?></b><?php endif; ?></a>
        <a href="?view=patients" class="<?= $view === 'patients' ? 'is-on' : '' ?>"><?= icon('shield', 18) ?> Patients &amp; Cards <?php if ($counts['patients']): ?><b><?= $counts['patients'] ?></b><?php endif; ?></a>
        <a href="?view=messages" class="<?= $view === 'messages' ? 'is-on' : '' ?>"><?= icon('inbox', 18) ?> Messages <?php if ($counts['unread']): ?><b><?= $counts['unread'] ?></b><?php endif; ?></a>
        <a href="?view=subscribers" class="<?= $view === 'subscribers' ? 'is-on' : '' ?>"><?= icon('mail', 18) ?> Subscribers</a>
        <a href="<?= url('mail/index.php') ?>" target="_blank"><?= icon('spark', 18) ?> Mail Portal ↗</a>
        <a href="?view=settings" class="<?= $view === 'settings' ? 'is-on' : '' ?>"><?= icon('lock', 18) ?> Settings</a>
      </nav>
      <div class="adm__foot">
        <a href="<?= url('index.php') ?>" target="_blank"><?= icon('arrow', 16) ?> View website</a>
        <a href="?logout=1"><?= icon('logout', 16) ?> Sign out (<?= e($_SESSION['admin']) ?>)</a>
      </div>
    </aside>

    <main class="adm__main">
      <?php if (getenv('RAILWAY_ENVIRONMENT') && !getenv('RAILWAY_VOLUME_MOUNT_PATH')): ?><div class="alert alert--error"><?= icon('alert', 18) ?> No Railway Volume is attached. Appointments and your admin login will be lost on the next deploy. Attach a Volume mounted at /data.</div><?php endif; ?>
      <?php if ($flash): ?><div class="alert alert--<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e($flash['msg']) ?></div><?php endif; ?>
      <div class="kpis">
        <div class="kpi"><span>New requests</span><strong><?= $counts['new'] ?></strong></div>
        <div class="kpi"><span>Last 7 days</span><strong><?= $counts['week'] ?></strong></div>
        <div class="kpi"><span>Total appointments</span><strong><?= $counts['all'] ?></strong></div>
        <div class="kpi"><span>Unread messages</span><strong><?= $counts['unread'] ?></strong></div>
        <div class="kpi"><span>Subscribers</span><strong><?= $counts['subs'] ?></strong></div>
        <div class="kpi"><span>Patients &amp; Cards</span><strong><?= $counts['patients'] ?></strong></div>
      </div>

<?php if ($view === 'appointments'):
  $where = []; $args = [];
  if (!empty($_GET['status']) && isset($badge[$_GET['status']])) { $where[] = 'status = ?'; $args[] = $_GET['status']; }
  if (!empty($_GET['loc']) && isset($LOCATIONS[$_GET['loc']])) { $where[] = 'location = ?'; $args[] = $_GET['loc']; }
  if (!empty($_GET['q'])) { $where[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR ref LIKE ?)'; $q = '%' . $_GET['q'] . '%'; array_push($args, $q, $q, $q, $q); }
  $st = $pdo->prepare('SELECT * FROM appointments' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 300');
  $st->execute($args); $rows = $st->fetchAll();
?>
      <div class="adm__head">
        <h1>Appointment requests</h1>
        <a class="btn btn--outline btn--sm" href="?export=appointments">Export CSV</a>
      </div>
      <form class="filters" method="get">
        <input type="hidden" name="view" value="appointments">
        <input class="input" type="search" name="q" placeholder="Search name, email, phone, ref…" value="<?= e($_GET['q'] ?? '') ?>">
        <select class="input" name="status"><option value="">All statuses</option><?php foreach ($badge as $s => $_): ?><option value="<?= $s ?>"<?= ($_GET['status'] ?? '') === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select class="input" name="loc"><option value="">All locations</option><?php foreach ($LOCATIONS as $k => $l): ?><option value="<?= $k ?>"<?= ($_GET['loc'] ?? '') === $k ? ' selected' : '' ?>><?= e($l['short']) ?></option><?php endforeach; ?></select>
        <button class="btn btn--primary btn--sm">Filter</button>
      </form>
      <?php if (!$rows): ?><div class="empty"><?= icon('calendar', 32) ?><p>No appointment requests yet.</p></div><?php else: ?>
      <div class="table-wrap"><table class="tbl">
        <thead><tr><th>Ref / Received</th><th>Patient</th><th>Service &amp; location</th><th>Preferred</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><span class="mono"><?= e($r['ref']) ?></span><small><?= e(date('M j, Y g:ia', strtotime($r['created_at'] . ' UTC'))) ?></small></td>
            <td><strong><?= e($r['full_name']) ?></strong><small><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></small><small><a href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a> · via <?= e($r['contact_method']) ?></small></td>
            <td><?= e($SERVICES[$r['service']]['title'] ?? $r['service']) ?><small><?= e($LOCATIONS[$r['location']]['short'] ?? $r['location']) ?> · <?= e(ucfirst($r['patient_type'])) ?> patient</small><?php if ($r['notes']): ?><details class="note"><summary>Note</summary><p><?= nl2br(e($r['notes'])) ?></p></details><?php endif; ?></td>
            <td><?= e(date('D, M j', strtotime($r['preferred_date']))) ?><small><?= e(ucfirst($r['preferred_time'])) ?></small></td>
            <td>
              <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                <select name="status" class="pill <?= $badge[$r['status']] ?? '' ?>" onchange="this.form.submit()"><?php foreach ($badge as $s => $_): ?><option value="<?= $s ?>"<?= $r['status'] === $s ? ' selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
              </form>
            </td>
            <td><form method="post" class="inline" onsubmit="return confirm('Delete this request?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_appt"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="icon-btn" title="Delete" aria-label="Delete"><?= icon('close', 16) ?></button></form></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>

<?php elseif ($view === 'patients'): 
  $where = []; $args = [];
  if (!empty($_GET['q'])) { $where[] = '(full_name LIKE ? OR card_number LIKE ? OR phone LIKE ?)'; $q = '%' . $_GET['q'] . '%'; array_push($args, $q, $q, $q); }
  $st = $pdo->prepare('SELECT * FROM patients' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 300');
  $st->execute($args); $rows = $st->fetchAll();
?>
      <div class="adm__head">
        <h1>Patient ID Cards &amp; Directory</h1>
        <a class="btn btn--outline btn--sm" href="<?= url('card.php') ?>" target="_blank"><?= icon('arrow', 14) ?> Public Card Lookup</a>
      </div>

      <div style="background:#fff;border:1px solid var(--line);border-radius:16px;padding:24px;margin-bottom:28px;box-shadow:var(--sh-1)">
        <h3 style="margin-bottom:16px;font-size:1.15rem"><?= icon('shield', 18) ?> Issue New Digital Patient Card</h3>
        <form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;align-items:flex-end">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add_patient">
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Full Name *</label>
            <input class="input" type="text" name="full_name" required placeholder="e.g. John Doe">
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Card ID (or leave blank for auto)</label>
            <input class="input" type="text" name="card_number" placeholder="PC-2026-XXXX">
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Date of Birth</label>
            <input class="input" type="date" name="dob">
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Gender</label>
            <select class="input" name="gender">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Blood Group</label>
            <select class="input" name="blood_group">
              <option value="O+">O+</option>
              <option value="O-">O-</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Phone Number</label>
            <input class="input" type="tel" name="phone" placeholder="+1 ...">
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Email Address</label>
            <input class="input" type="email" name="email" placeholder="patient@example.com">
          </div>
          <div>
            <label style="display:block;font-size:.82rem;font-weight:700;margin-bottom:6px">Emergency Contact</label>
            <input class="input" type="text" name="emergency_contact" placeholder="Name &amp; Phone">
          </div>
          <div style="grid-column:1 / -1;display:flex;justify-content:flex-end">
            <button class="btn btn--primary" type="submit"><?= icon('check', 16) ?> Issue &amp; Generate Card</button>
          </div>
        </form>
      </div>

      <form class="filters" method="get">
        <input type="hidden" name="view" value="patients">
        <input class="input" type="search" name="q" placeholder="Search patient name, card ID, phone…" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn btn--primary btn--sm">Search</button>
      </form>

      <?php if (!$rows): ?><div class="empty"><?= icon('users', 32) ?><p>No registered patients yet. Use the form above to issue the first card.</p></div><?php else: ?>
      <div class="table-wrap"><table class="tbl">
        <thead><tr><th>Card ID</th><th>Patient Name</th><th>DOB / Blood</th><th>Contact</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><strong class="mono" style="color:var(--teal)"><?= e($r['card_number']) ?></strong><small>Issued <?= e($r['issue_date'] ?: date('Y-m-d', strtotime($r['created_at']))) ?></small></td>
            <td><strong><?= e($r['full_name']) ?></strong><small><?= e($r['gender']) ?></small></td>
            <td><?= e($r['dob'] ?: 'N/A') ?><small>Blood: <strong><?= e($r['blood_group'] ?: 'N/A') ?></strong></small></td>
            <td><small><?= e($r['phone']) ?></small><small><?= e($r['email']) ?></small></td>
            <td><span class="pill b-confirmed"><?= e(strtoupper($r['status'])) ?></span></td>
            <td>
              <div style="display:flex;gap:8px;align-items:center">
                <a class="btn btn--outline btn--sm" href="<?= url('card.php?id=' . urlencode($r['card_number'])) ?>" target="_blank"><?= icon('arrow', 14) ?> View Card</a>
                <form method="post" class="inline" onsubmit="return confirm('Delete this patient card record?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="del_patient">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button class="icon-btn" title="Delete" aria-label="Delete"><?= icon('close', 16) ?></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>

<?php elseif ($view === 'messages'): $rows = $pdo->query('SELECT * FROM messages ORDER BY is_read ASC, id DESC LIMIT 300')->fetchAll(); ?>
      <div class="adm__head"><h1>Messages</h1><a class="btn btn--outline btn--sm" href="?view=messages&export=messages">Export CSV</a></div>
      <?php if (!$rows): ?><div class="empty"><?= icon('inbox', 32) ?><p>No messages yet.</p></div><?php else: ?>
      <div class="msgs">
        <?php foreach ($rows as $m): ?>
        <article class="msg<?= $m['is_read'] ? '' : ' is-unread' ?>">
          <header>
            <div><strong><?= e($m['full_name']) ?></strong> <span class="muted">· <?= e($m['subject'] ?: 'General') ?><?= $m['location'] ? ' · ' . e($LOCATIONS[$m['location']]['short'] ?? '') : '' ?></span></div>
            <small><?= e(date('M j, Y g:ia', strtotime($m['created_at'] . ' UTC'))) ?></small>
          </header>
          <p><?= nl2br(e($m['message'])) ?></p>
          <footer>
            <a class="btn btn--primary btn--sm" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: 'Your message to ' . SITE_NAME)) ?>">Reply</a>
            <?php if ($m['phone']): ?><a class="btn btn--outline btn--sm" href="tel:<?= e($m['phone']) ?>">Call <?= e($m['phone']) ?></a><?php endif; ?>
            <?php if (!$m['is_read']): ?><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn--text btn--sm">Mark as read</button></form><?php endif; ?>
            <form method="post" class="inline" onsubmit="return confirm('Delete this message?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_msg"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn--text btn--sm danger">Delete</button></form>
          </footer>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

<?php elseif ($view === 'subscribers'): $rows = $pdo->query('SELECT * FROM subscribers ORDER BY id DESC')->fetchAll(); ?>
      <div class="adm__head"><h1>Newsletter subscribers</h1><a class="btn btn--outline btn--sm" href="?view=subscribers&export=subscribers">Export CSV</a></div>
      <?php if (!$rows): ?><div class="empty"><?= icon('mail', 32) ?><p>No subscribers yet.</p></div><?php else: ?>
      <div class="table-wrap"><table class="tbl"><thead><tr><th>Email</th><th>Subscribed</th><th></th></tr></thead><tbody>
        <?php foreach ($rows as $s): ?><tr><td><?= e($s['email']) ?></td><td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td><td><form method="post" class="inline" onsubmit="return confirm('Remove subscriber?')"><?= csrf_field() ?><input type="hidden" name="action" value="del_sub"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="icon-btn" aria-label="Remove"><?= icon('close', 16) ?></button></form></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <?php endif; ?>

<?php else: ?>
      <div class="adm__head"><h1>Settings</h1></div>
      <form class="form-card narrow" method="post">
        <h2>Change password</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <div class="field"><label class="field__label" for="np">New password</label><input class="input" id="np" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
        <div class="field"><label class="field__label" for="np2">Confirm new password</label><input class="input" id="np2" type="password" name="password2" minlength="8" required autocomplete="new-password"></div>
        <button class="btn btn--primary">Update password</button>
      </form>
      <form class="form-card narrow" method="post" style="margin-top:20px">
        <h2>Email notifications</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="testmail">
        <dl class="summary">
          <div><dt>Delivery method</dt><dd><?= e(MAIL_DRIVER) ?></dd></div>
          <div><dt>Sends to</dt><dd><?= e(SITE_EMAIL) ?></dd></div>
          <div><dt>Sends from</dt><dd><?= e(MAIL_FROM) ?></dd></div>
          <div><dt>Database</dt><dd><?= e(DB_PATH) ?></dd></div>
        </dl>
        <button class="btn btn--primary"<?= MAIL_DRIVER === 'none' ? ' disabled' : '' ?>>Send test email</button>
        <p class="field__hint" style="margin-top:14px">These come from environment variables (Railway → Variables) or <code>includes/config.php</code>. Every submission is saved here, even when email is off.</p>
      </form>
<?php endif; ?>
    </main>
  </div>
<?php endif; ?>
</body>
</html>
