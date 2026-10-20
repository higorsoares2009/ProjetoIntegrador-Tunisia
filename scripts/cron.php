<?php
/**
 * Rotina periódica (publicações agendadas + backup diário). Somente terminal.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit('Not found'); }
require_once __DIR__ . '/../includes/scheduler.php';

$pdo = getPDO();
if (!$pdo) { fwrite(STDERR, "Sem conexão com o banco.\n"); exit(1); }

$r = runScheduledJobs($pdo);
echo date('c') . " publicadas={$r['published']}";
if ($r['backup'] !== null) {
    echo $r['backup']['ok'] ? " backup={$r['backup']['file']}" : " backup=ERRO({$r['backup']['error']})";
}
echo "\n";
exit(($r['backup']['ok'] ?? true) ? 0 : 1);
