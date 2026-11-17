<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit('CLI only'); }
if (PHP_VERSION_ID < 70400) { exit("PHP 7.4 or newer required\n"); }
$config = $argv[1] ?? '';
if ($config === '--offline') {
    require __DIR__ . '/offline.php';
} else {
if (!is_file($config)) { exit("Usage: php build.transport.php /path/to/modx/config.core.php\n"); }
require $config;
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('mgr');
if ((int) ($modx->getVersionData()['version'] ?? 0) !== 2) { throw new RuntimeException('MODX 2 required'); }
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
}
$builder->createPackage('llmsforge', '1.0.1', 'pl');
$namespace = $modx->newObject('modNamespace');
$namespace->fromArray(['name' => 'llmsforge', 'path' => '{core_path}components/llmsforge/', 'assets_path' => '{assets_path}components/llmsforge/'], '', true);
$builder->registerNamespace($namespace, false, true, '{core_path}components/llmsforge/');
$checkEvent = $modx->newObject(\modEvent::class);
$checkEvent->set('name', 'OnLLMSForgeResourceCheck');
$checkEvent->set('service', 1);
$checkEvent->set('groupname', 'LLMS Forge');
if ($checkEvent->get('name') !== 'OnLLMSForgeResourceCheck') {
    throw new RuntimeException('Cannot build resource check event: missing event name');
}
$builder->putVehicle($builder->createVehicle($checkEvent, [xPDOTransport::UNIQUE_KEY => 'name', xPDOTransport::PRESERVE_KEYS => true, xPDOTransport::UPDATE_OBJECT => true]));
$source = dirname(__DIR__) . '/core/components/llmsforge';
$snippet = $modx->newObject(modSnippet::class);
$snippet->fromArray([
    'name' => 'LLMSForge',
    'description' => 'Curated llms.txt for MODX 2. Use uncached in a text/plain resource.',
    'snippet' => preg_replace('/^<\?php\s*/', '', file_get_contents($source . '/elements/snippets/llmsforge.php')),
], '', true);
$vehicle = $builder->createVehicle($snippet, [xPDOTransport::UNIQUE_KEY => 'name', xPDOTransport::PRESERVE_KEYS => false, xPDOTransport::UPDATE_OBJECT => true]);
$vehicle->resolve('file', ['source' => $source, 'target' => "return MODX_CORE_PATH . 'components/';"]);
$vehicle->resolve('file', ['source' => dirname(__DIR__) . '/assets/components/llmsforge', 'target' => "return MODX_ASSETS_PATH . 'components/';"]);
$builder->putVehicle($vehicle);
$menu = $modx->newObject(\modMenu::class);
$menu->fromArray(['text' => 'LLMS Forge', 'parent' => 'components', 'action' => 'index', 'namespace' => 'llmsforge', 'description' => 'Генератор llms.txt', 'permissions' => 'settings'], '', true);
$builder->putVehicle($builder->createVehicle($menu, [xPDOTransport::UNIQUE_KEY => 'text', xPDOTransport::PRESERVE_KEYS => true, xPDOTransport::UPDATE_OBJECT => true]));
$plugin = $modx->newObject(\modPlugin::class);
$plugin->fromArray(['name' => 'LLMSForgeRouter', 'plugincode' => preg_replace('/^<\?php\s*/', '', file_get_contents($source . '/elements/plugins/router.php')), 'disabled' => 0], '', true);
$event = $modx->newObject(\modPluginEvent::class);
$event->fromArray(['event' => 'OnPageNotFound', 'priority' => 0], '', true);
$pluginEvents = [$event];
$plugin->addMany($pluginEvents, 'PluginEvents');
$builder->putVehicle($builder->createVehicle($plugin, [xPDOTransport::UNIQUE_KEY => 'name', xPDOTransport::PRESERVE_KEYS => false, xPDOTransport::UPDATE_OBJECT => true, xPDOTransport::RELATED_OBJECTS => true, xPDOTransport::RELATED_OBJECT_ATTRIBUTES => ['PluginEvents' => [xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'], xPDOTransport::PRESERVE_KEYS => true, xPDOTransport::UPDATE_OBJECT => true]]]));
require_once $source . '/src/Manager.php';
$tracking = $modx->newObject(\modPlugin::class);
$tracking->fromArray(['name' => 'LLMSForgeVisits', 'plugincode' => preg_replace('/^<\?php\s*/', '', file_get_contents($source . '/elements/plugins/visits.php')), 'disabled' => 0], '', true);
$trackingEvent = $modx->newObject(\modPluginEvent::class);
$trackingEvent->fromArray(['event' => 'OnHandleRequest', 'priority' => -1000], '', true);
$trackingEvents = [$trackingEvent];
$tracking->addMany($trackingEvents, 'PluginEvents');
$builder->putVehicle($builder->createVehicle($tracking, [xPDOTransport::UNIQUE_KEY => 'name', xPDOTransport::PRESERVE_KEYS => false, xPDOTransport::UPDATE_OBJECT => true, xPDOTransport::RELATED_OBJECTS => true, xPDOTransport::RELATED_OBJECT_ATTRIBUTES => ['PluginEvents' => [xPDOTransport::UNIQUE_KEY => ['pluginid', 'event'], xPDOTransport::PRESERVE_KEYS => true, xPDOTransport::UPDATE_OBJECT => true]]]));
foreach (\LLMSForge\Manager::DEFAULTS as $name => $value) {
    $setting = $modx->newObject(modSystemSetting::class);
    $setting->fromArray(['key' => 'llmsforge.' . $name, 'value' => $value, 'xtype' => 'textfield', 'namespace' => 'llmsforge', 'area' => 'general'], '', true);
    $builder->putVehicle($builder->createVehicle($setting, [xPDOTransport::UNIQUE_KEY => 'key', xPDOTransport::PRESERVE_KEYS => true, xPDOTransport::UPDATE_OBJECT => false]));
}
$builder->setPackageAttributes([
    'license' => file_get_contents(dirname(__DIR__) . '/COPYING'),
    'readme' => file_get_contents(dirname(__DIR__) . '/README.md'),
    'changelog' => file_get_contents(dirname(__DIR__) . '/CHANGELOG.md'),
]);
if (!$builder->pack()) { throw new RuntimeException('Package build failed'); }
echo "Built " . $builder->directory . $builder->filename . "\n";
