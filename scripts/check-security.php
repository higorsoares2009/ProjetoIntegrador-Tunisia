<?php
/**
 * Verificação de segurança pós-instalação (somente terminal):
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute apenas pelo terminal.');
}
require_once __DIR__ . '/../config/database.php';

$problems = 0;
$pdo = getPDO();
if (!$pdo) {
    fwrite(STDERR, "[ERRO] Sem conexão com o banco — verifique o .env.\n");
    exit(2);
}

$legacy = $pdo->prepare("SELECT id, role, status FROM users WHERE email = ?");
foreach (['admin@projetotunisia.local', 'adm@adm.com'] as $legacyEmail) {
$legacy->execute([$legacyEmail]);
if ($row = $legacy->fetch()) {
    $problems++;
    echo "[ALERTA] A conta de demonstração {$legacyEmail} ainda existe (ID {$row['id']}, {$row['role']}, {$row['status']}).\n";
    echo "         Sua senha era pública nas versões antigas. Crie seu administrador com admin/create_admin.php e depois:\n";
    echo "         DELETE FROM users WHERE email = '{$legacyEmail}';\n";
}
}

if (APP_ENV !== 'production') {
    $problems++;
    echo "[ALERTA] APP_ENV=" . APP_ENV . ". Em produção use APP_ENV=production e APP_DEBUG=false.\n";
}
if (APP_DEBUG) {
    $problems++;
    echo "[ALERTA] APP_DEBUG está ligado: erros podem ser exibidos aos visitantes.\n";
}
if (DB_USER === 'root' && DB_PASS === '') {
    $problems++;
    echo "[ALERTA] O banco usa 'root' sem senha. Crie um usuário MySQL dedicado e com senha.\n";
}
if (envValue('FORCE_HTTPS', '0') !== '1') {
    echo "[AVISO]  FORCE_HTTPS=0. Ative em produção (com certificado instalado).\n";
}
if (trim((string)envValue('PUBLIC_URL', '')) === '') {
    echo "[AVISO]  PUBLIC_URL vazio: canonical/sitemap serão deduzidos da requisição. Defina a URL final em produção.\n";
}

foreach ([UPLOADS_DIR, CACHE_DIR] as $dir) {
    if (!is_dir($dir) || !is_writable($dir)) {
        $problems++;
        echo "[ALERTA] Pasta sem permissão de escrita: " . (realpath($dir) ?: $dir) . "\n";
    }
}

echo $problems === 0 ? "OK: nenhum problema crítico encontrado.\n" : "\n{$problems} problema(s) encontrado(s).\n";
exit($problems === 0 ? 0 : 1);
