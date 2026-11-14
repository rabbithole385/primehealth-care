<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ref TEXT UNIQUE,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        location TEXT NOT NULL,
        service TEXT NOT NULL,
        patient_type TEXT,
        preferred_date TEXT,
        preferred_time TEXT,
        contact_method TEXT,
        notes TEXT,
        status TEXT DEFAULT 'new',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        location TEXT,
        subject TEXT,
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS subscribers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        card_number TEXT UNIQUE NOT NULL,
        full_name TEXT NOT NULL,
        dob TEXT,
        gender TEXT,
        blood_group TEXT,
        phone TEXT,
        email TEXT,
        emergency_contact TEXT,
        address TEXT,
        issue_date TEXT,
        expiry_date TEXT,
        status TEXT DEFAULT 'active',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    return $pdo;
}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): bool { return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']); }

/** Simple per-session throttle: max $max submissions per $window seconds per form key */
function throttle(string $key, int $max = 5, int $window = 600): bool {
    $now = time();
    $_SESSION['throttle'][$key] = array_values(array_filter($_SESSION['throttle'][$key] ?? [], fn($t) => $t > $now - $window));
    if (count($_SESSION['throttle'][$key]) >= $max) return false;
    $_SESSION['throttle'][$key][] = $now;
    return true;
}

function clean(string $k, int $max = 500): string { return mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max); }

/** Send a notification email to SITE_EMAIL. Never throws — a failed email must not lose a submission. */
function notify(string $subject, string $body, string $replyTo = ''): bool {
    $replyTo = filter_var($replyTo, FILTER_VALIDATE_EMAIL) ? $replyTo : '';
    try {
        switch (MAIL_DRIVER) {
            case 'resend': return send_resend($subject, $body, $replyTo);
            case 'smtp':   return send_smtp($subject, $body, $replyTo);
            case 'mail':
                $headers = "From: " . SITE_NAME . " <" . MAIL_FROM . ">\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                if ($replyTo) $headers .= "Reply-To: {$replyTo}\r\n";
                return @mail(SITE_EMAIL, $subject, $body, $headers);
            default: return false;
        }
    } catch (Throwable $e) {
        error_log('[primecare mail] ' . $e->getMessage());
        return false;
    }
}

/** Resend.com HTTPS API — works on Railway Free/Hobby (SMTP is blocked there). */
function send_resend(string $subject, string $body, string $replyTo): bool {
    $payload = ['from' => SITE_NAME . ' <' . MAIL_FROM . '>', 'to' => array_map('trim', explode(',', SITE_EMAIL)), 'subject' => $subject, 'text' => $body];
    if ($replyTo) $payload['reply_to'] = $replyTo;
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . env('RESEND_API_KEY'), 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);
    $res = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code >= 300) error_log("[primecare mail] Resend HTTP {$code}: {$res}");
    return $code > 0 && $code < 300;
}

/** Minimal SMTP client (STARTTLS on 587, implicit TLS on 465). No Composer needed. */
function send_smtp(string $subject, string $body, string $replyTo): bool {
    $host = env('SMTP_HOST'); $port = (int)env('SMTP_PORT', 587);
    $user = env('SMTP_USER'); $pass = env('SMTP_PASS');
    $fp = @stream_socket_client(($port === 465 ? 'ssl://' : 'tcp://') . "$host:$port", $errno, $errstr, 10);
    if (!$fp) throw new RuntimeException("SMTP connect failed: $errstr");
    stream_set_timeout($fp, 10);
    $read = function () use ($fp) { $d = ''; while (($l = fgets($fp, 515)) !== false) { $d .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $d; };
    $cmd = function (string $c, array $ok) use ($fp, $read) {
        if ($c !== '') fwrite($fp, $c . "\r\n");
        $r = $read();
        if (!in_array((int)substr($r, 0, 3), $ok, true)) throw new RuntimeException('SMTP error: ' . trim($r));
        return $r;
    };
    $ehlo = 'EHLO ' . (parse_url(SITE_URL, PHP_URL_HOST) ?: 'localhost');
    $cmd('', [220]); $cmd($ehlo, [250]);
    if ($port !== 465) { $cmd('STARTTLS', [220]); stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT); $cmd($ehlo, [250]); }
    if ($user !== '') { $cmd('AUTH LOGIN', [334]); $cmd(base64_encode($user), [334]); $cmd(base64_encode($pass), [235]); }
    $cmd('MAIL FROM:<' . MAIL_FROM . '>', [250]);
    foreach (array_map('trim', explode(',', SITE_EMAIL)) as $to) $cmd("RCPT TO:<{$to}>", [250, 251]);
    $cmd('DATA', [354]);
    $h = 'From: ' . SITE_NAME . ' <' . MAIL_FROM . ">\r\nTo: " . SITE_EMAIL . "\r\nSubject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
       . 'Date: ' . date('r') . "\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n"
       . ($replyTo ? "Reply-To: {$replyTo}\r\n" : '');
    $msg = preg_replace('/^\./m', '..', str_replace(["\r\n", "\n"], ["\n", "\r\n"], $body));
    $cmd($h . "\r\n" . $msg . "\r\n.", [250]);
    fwrite($fp, "QUIT\r\n"); fclose($fp);
    return true;
}

function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function get_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

function url(string $path = ''): string {
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if (basename($base) === 'admin') $base = dirname($base);
    $base = $base === '/' || $base === '.' ? '' : $base;
    return $base . '/' . ltrim($path, '/');
}

/** Inline SVG icon set (stroke icons, inherit currentColor) */
function icon(string $name, int $size = 24): string {
    $p = [
        'stethoscope' => '<path d="M6 3v6a4 4 0 0 0 8 0V3"/><path d="M4 3h4M12 3h4"/><path d="M10 13v2a5 5 0 0 0 10 0v-2"/><circle cx="20" cy="11" r="2"/>',
        'shield'      => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path d="m9 12 2 2 4-4"/>',
        'pill'        => '<rect x="3" y="9" width="18" height="6" rx="3" transform="rotate(-45 12 12)"/><path d="m8.5 8.5 7 7"/>',
        'microscope'  => '<path d="M6 18h8M3 22h18M14 22a7 7 0 1 0 0-14h-1"/><path d="M9 14h2M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2z"/><path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3"/>',
        'heart'       => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/><path d="M3.5 12h4l2-3 3 6 2-3h6"/>',
        'flower'      => '<circle cx="12" cy="9" r="3"/><path d="M12 12v9M9 18c-3 0-5-2-5-5 3 0 5 2 5 5zM15 18c3 0 5-2 5-5-3 0-5 2-5 5z"/><path d="M12 6V3M9.4 7.5 7 6M14.6 7.5 17 6"/>',
        'child'       => '<circle cx="12" cy="5" r="2.5"/><path d="M8 21v-6l-2-4 6-2 6 2-2 4v6M10 21v-4h4v4"/>',
        'hands'       => '<path d="M11 14h2a2 2 0 1 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 16"/><path d="m7 20 1.6-1.4c.3-.4.8-.6 1.4-.6h4c1.1 0 2.1-.4 2.8-1.2l4.6-4.4a2 2 0 0 0-2.8-2.8l-4.2 3.9"/><path d="m2 15 6 6"/>',
        'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
        'pin'         => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'clock'       => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
        'calendar'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>',
        'arrow'       => '<path d="M5 12h14M13 5l7 7-7 7"/>',
        'check'       => '<path d="M20 6 9 17l-5-5"/>',
        'menu'        => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'close'       => '<path d="M18 6 6 18M6 6l12 12"/>',
        'plus'        => '<path d="M12 5v14M5 12h14"/>',
        'alert'       => '<path d="m10.3 3.9-8.2 14A2 2 0 0 0 3.8 21h16.4a2 2 0 0 0 1.7-3.1l-8.2-14a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'compass'     => '<circle cx="12" cy="12" r="10"/><path d="m16.2 7.8-2.1 6.3-6.3 2.1 2.1-6.3z"/>',
        'star'        => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'users'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'spark'       => '<path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/>',
        'scale'       => '<path d="M12 3v18M7 21h10M5 7h14M5 7l-3 7a3 3 0 0 0 6 0zM19 7l-3 7a3 3 0 0 0 6 0z"/>',
        'video'       => '<path d="m23 7-7 5 7 5z"/><rect x="1" y="5" width="15" height="14" rx="2"/>',
        'lock'        => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'logout'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'inbox'       => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>',
        'quote'       => '<path d="M3 21c3 0 7-1 7-8V5c0-1.2-.8-2-2-2H4c-1.2 0-2 .8-2 2v6c0 1.2.8 2 2 2h3c0 3-1 5-4 5zM15 21c3 0 7-1 7-8V5c0-1.2-.8-2-2-2h-4c-1.2 0-2 .8-2 2v6c0 1.2.8 2 2 2h3c0 3-1 5-4 5z"/>',
    ];
    $d = $p[$name] ?? $p['plus'];
    return '<svg class="ic" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}
