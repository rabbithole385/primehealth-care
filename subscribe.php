<?php
require_once __DIR__ . '/includes/functions.php';
$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
$respond = function (bool $ok, string $msg) use ($ajax) {
    if ($ajax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok, 'message' => $msg]); exit; }
    flash($ok ? 'success' : 'error', $msg);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? url('index.php')));
    exit;
};
if ($_SERVER['REQUEST_METHOD'] !== 'POST') $respond(false, 'Invalid request.');
if (!csrf_check() || !empty($_POST['website'])) $respond(false, 'Please refresh the page and try again.');
$email = clean('email', 160);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $respond(false, 'Please enter a valid email address.');
if (!throttle('sub', 5)) $respond(false, 'Too many attempts. Please try later.');
db()->prepare('INSERT OR IGNORE INTO subscribers (email) VALUES (?)')->execute([strtolower($email)]);
$respond(true, 'You’re subscribed. Thank you!');
