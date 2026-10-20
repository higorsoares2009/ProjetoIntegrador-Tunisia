<?php
define('NO_SESSION', true);
require_once __DIR__ . '/includes/scheduler.php';

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

$expected = (string)envValue('CRON_TOKEN', '');
$given = (string)($_GET['token'] ?? '');
if (strlen($expected) < 16 || !hash_equals($expected, $given)) {
    http_response_code(403);
    exit("Forbidden\n");
}
$pdo = getPDO();
if (!$pdo) { http_response_code(500); exit("db\n"); }

$r = runScheduledJobs($pdo);
echo "ok publicadas={$r['published']}";
if ($r['backup'] !== null) echo $r['backup']['ok'] ? ' backup=ok' : ' backup=erro';
echo "\n";
