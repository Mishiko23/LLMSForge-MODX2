<?php
declare(strict_types=1);

// Development-only integration check. Use on a disposable MODX 2 installation.
if (PHP_SAPI !== 'cli' || count($argv) !== 3 || $argv[1] !== '--install') {
    exit("Usage: php smoke.install.php --install /path/to/config.core.php\n");
}
require $argv[2];
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new \modX();
$modx->initialize('mgr');
$package = $modx->getObject(\modTransportPackage::class, ['signature' => 'llmsforge-1.0.0-pl']);
if (!$package) {
    $package = $modx->newObject(\modTransportPackage::class);
    $package->fromArray(['signature' => 'llmsforge-1.0.0-pl', 'package_name' => 'llmsforge', 'version_major' => 1, 'version_minor' => 0, 'version_patch' => 0, 'release' => 'pl', 'state' => 1, 'workspace' => 1, 'created' => date('Y-m-d H:i:s')], '', true);
    if (!$package->save()) { throw new RuntimeException('Could not register test package'); }
}
if (!$package->install()) { throw new RuntimeException('Transport install failed; inspect MODX error log'); }
foreach ([
    [\modMenu::class, ['text' => 'LLMS Forge']],
    [\modSnippet::class, ['name' => 'LLMSForge']],
    [\modPlugin::class, ['name' => 'LLMSForgeRouter']],
    [\modPlugin::class, ['name' => 'LLMSForgeVisits']],
    [\modEvent::class, ['name' => 'OnLLMSForgeResourceCheck']],
] as [$class, $criteria]) {
    if (!$modx->getObject($class, $criteria)) { throw new RuntimeException('Missing object: ' . json_encode($criteria)); }
}
echo "Transport install and object checks passed\n";
