<?php
declare(strict_types=1);
// Uses the real MODX 2 model and transport serializer; never loads site credentials.
$core = realpath($argv[2] ?? '');
if (!$core || !is_file($core . '/model/modx/modx.class.php')) { throw new RuntimeException('Usage: php _build/build.transport.php --offline /path/to/modx2/core'); }
define('MODX_CORE_PATH', str_replace('\\', '/', $core) . '/');
require MODX_CORE_PATH . 'model/modx/modx.class.php';
class LLMSForgeBuildModx extends modX
{
    protected function loadConfig($configPath = '', $data = [], $driverOptions = []) {
        return [
            xPDO::OPT_CONNECTIONS => [['dsn' => 'mysql:host=127.0.0.1;dbname=unused;charset=utf8', 'username' => '', 'password' => '', 'options' => [], 'driverOptions' => []]],
            xPDO::OPT_TABLE_PREFIX => 'modx_',
            xPDO::OPT_CACHE_PATH => dirname(__DIR__) . '/dist/build-cache/',
            'core_path' => MODX_CORE_PATH,
            'log_target' => 'ECHO',
        ];
    }
    public function connect($driverOptions = [], array $options = []) { throw new RuntimeException('Database access forbidden in offline build'); }
    public function lexicon($key, $params = [], $language = '') { return $key; }
}
$modx = new LLMSForgeBuildModx();
if ((int) $modx->getVersionData()['version'] !== 2) { throw new RuntimeException('MODX 2 required'); }
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$modx->loadClass('transport.modTransportVehicle', '', false, true);
$modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);
// The normal constructor selects a database workspace; offline builds use dist.
$builder = (new ReflectionClass('modPackageBuilder'))->newInstanceWithoutConstructor();
$builder->modx = $modx;
$builder->directory = str_replace('\\', '/', dirname(__DIR__)) . '/dist/';
$builder->autoselects = [];
if (!is_dir($builder->directory)) { mkdir($builder->directory, 0755, true); }
