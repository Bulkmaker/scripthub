<?php

declare(strict_types=1);

/**
 * scriptHub — Auto-registration script
 *
 * Creates namespace, menu, plugin, system settings, and DB table.
 *
 * Usage: docker compose exec -u www-data app php core/components/scripthub/build/install.php
 */

require_once dirname(__FILE__, 5) . '/config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';

use MODX\Revolution\modX;
use MODX\Revolution\modNamespace;
use MODX\Revolution\modMenu;
use MODX\Revolution\modPlugin;
use MODX\Revolution\modPluginEvent;
use MODX\Revolution\modSystemSetting;

$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$corePath = MODX_CORE_PATH . 'components/scripthub/';
$assetsUrl = MODX_ASSETS_URL . 'components/scripthub/';

echo "=== scriptHub Installer ===\n\n";

// --- 1. Namespace ---
echo "[1/5] Namespace... ";
$namespace = $modx->getObject(modNamespace::class, 'scripthub');
if (!$namespace) {
    $namespace = $modx->newObject(modNamespace::class);
    $namespace->set('name', 'scripthub');
    $namespace->set('path', '{core_path}components/scripthub/');
    $namespace->set('assets_path', '{assets_path}components/scripthub/');
    $namespace->save();
    echo "CREATED\n";
} else {
    echo "EXISTS\n";
}

// --- 2. System Settings ---
echo "[2/5] System settings... ";
$settings = [
    'scripthub.core_path' => [
        'value' => '{core_path}components/scripthub/',
        'xtype' => 'textfield',
        'area' => 'paths',
    ],
    'scripthub.assets_url' => [
        'value' => '{assets_url}components/scripthub/',
        'xtype' => 'textfield',
        'area' => 'paths',
    ],
];

$created = 0;
foreach ($settings as $key => $data) {
    if (!$modx->getObject(modSystemSetting::class, $key)) {
        $setting = $modx->newObject(modSystemSetting::class);
        $setting->set('key', $key);
        $setting->set('value', $data['value']);
        $setting->set('xtype', $data['xtype']);
        $setting->set('namespace', 'scripthub');
        $setting->set('area', $data['area']);
        $setting->save();
        $created++;
    }
}
echo "{$created} created\n";

// --- 3. Menu ---
echo "[3/5] Menu... ";
$menu = $modx->getObject(modMenu::class, ['text' => 'scripthub']);
if (!$menu) {
    $menu = $modx->newObject(modMenu::class);
    $menu->set('text', 'scripthub');
    $menu->set('parent', 'components');
    $menu->set('action', 'home');
    $menu->set('description', 'scripthub_menu_desc');
    $menu->set('namespace', 'scripthub');
    $menu->set('menuindex', 0);
    $menu->save();
    echo "CREATED\n";
} else {
    echo "EXISTS\n";
}

// --- 4. Plugin ---
echo "[4/5] Plugin... ";
$plugin = $modx->getObject(modPlugin::class, ['name' => 'scriptHub']);
if (!$plugin) {
    $plugin = $modx->newObject(modPlugin::class);
    $plugin->set('name', 'scriptHub');
    $plugin->set('description', 'scriptHub — управление внешними скриптами');
    $plugin->set('plugincode', '');
    $plugin->set('disabled', false);
    $plugin->set('category', 0);
    $plugin->set('static', true);
    $plugin->set('static_file', 'core/components/scripthub/elements/plugins/plugin.scripthub.php');
    $plugin->save();

    // Register events
    $events = ['OnMODXInit', 'OnWebPagePrerender'];
    foreach ($events as $eventName) {
        $event = $modx->newObject(modPluginEvent::class);
        $event->set('pluginid', $plugin->get('id'));
        $event->set('event', $eventName);
        $event->set('priority', 0);
        $event->save();
    }
    echo "CREATED (events: " . implode(', ', $events) . ")\n";
} else {
    echo "EXISTS\n";
}

// --- 5. DB Table ---
echo "[5/5] Database table... ";
$modx->addPackage('scripthub', $corePath . 'model/');
$manager = $modx->getManager();

$tableName = $modx->getTableName(\scripthub\ScriptHubService::class);
$stmt = $modx->prepare("SHOW TABLES LIKE " . $modx->quote(str_replace('`', '', $tableName)));
if ($stmt && $stmt->execute() && $stmt->rowCount() === 0) {
    $manager->createObjectContainer(\scripthub\ScriptHubService::class);
    echo "CREATED\n";
} else {
    echo "EXISTS\n";
}

// --- Clear cache ---
echo "\nClearing cache... ";
$modx->getCacheManager()->refresh();
echo "DONE\n";

echo "\n=== Installation complete! ===\n";
echo "Open: Manager → Extras → scriptHub\n";
