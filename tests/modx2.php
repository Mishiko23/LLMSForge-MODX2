<?php
declare(strict_types=1);
// Loads real MODX 2 classes without initializing MODX or connecting to a database.
$core = $argv[1] ?? '';
if (!is_file($core . '/model/modx/modx.class.php')) { exit("Usage: php tests/modx2.php /path/to/modx/core\n"); }
define('MODX_CORE_PATH', rtrim($core, '/\\') . '/');
require MODX_CORE_PATH . 'model/modx/modx.class.php';
require MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';
require dirname(__DIR__) . '/core/components/llmsforge/controllers/index.class.php';
require dirname(__DIR__) . '/core/components/llmsforge/src/Manager.php';
require dirname(__DIR__) . '/core/components/llmsforge/src/Generator.php';
$modx = (new ReflectionClass('modX'))->newInstanceWithoutConstructor();
new LLMSForge\Manager($modx);
new LLMSForge\Generator($modx);
new LLMSForge\Documents($modx);
if (!is_subclass_of('LLMSForgeIndexManagerController', 'modExtraManagerController')) { throw new RuntimeException('Wrong controller parent'); }
echo "Real MODX 2 class compatibility checks passed\n";
