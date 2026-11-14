<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json'); header('Cache-Control: no-store');
try { db()->query('SELECT 1'); echo json_encode(['ok' => true]); }
catch (Throwable $e) { http_response_code(503); echo json_encode(['ok' => false]); }
