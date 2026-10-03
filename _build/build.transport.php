<?php

declare(strict_types=1);

/**
 * scriptHub — transport package builder.
 *
 * Производит scripthub-<version>-<release>.transport.zip готовый к
 * установке через MODX Manager → Package Management → Upload Package и к
 * заливке в modstore.pro.
 *
 * Запуск:
 *   docker compose exec -u www-data app php /var/www/html/_build/build.transport.php
 *
 * Артефакт окажется в MODX_CORE_PATH/packages/.
 */

use MODX\Revolution\modX;
use MODX\Revolution\modCategory;
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modMenu;
use MODX\Revolution\modPlugin;
use MODX\Revolution\Transport\modPackageBuilder;
use xPDO\Transport\xPDOTransport;

$buildRoot  = __DIR__;
$sourceRoot = dirname(__FILE__, 2);

// --- 1. Local build config ---
if (!file_exists($buildRoot . '/build.config.php')) {
    copy($buildRoot . '/build.config.sample.php', $buildRoot . '/build.config.php');
}
require_once $buildRoot . '/build.config.php';

if (!defined('SCRIPTHUB_VERSION') || !defined('SCRIPTHUB_RELEASE')) {
    fwrite(STDERR, "ERROR: SCRIPTHUB_VERSION / SCRIPTHUB_RELEASE не определены в build.config.php\n");
    exit(1);
}

// --- 2. Locate config.core.php ---
$configCoreLocated = false;
if (defined('SCRIPTHUB_MODX_CORE_PATH') && SCRIPTHUB_MODX_CORE_PATH) {
    $forced = rtrim((string) SCRIPTHUB_MODX_CORE_PATH, '/') . '/config.core.php';
    if (file_exists($forced)) {
        require_once $forced;
        $configCoreLocated = true;
    }
}
if (!$configCoreLocated) {
    $candidates = [
        $buildRoot . '/../config.core.php',
        $buildRoot . '/../../config.core.php',
        $buildRoot . '/../../../config.core.php',
        $buildRoot . '/../../../../config.core.php',
        $buildRoot . '/../../../../../config.core.php',
    ];
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            require_once $candidate;
            $configCoreLocated = true;
            break;
        }
    }
}

if (!$configCoreLocated || !defined('MODX_CORE_PATH')) {
    fwrite(STDERR, "ERROR: config.core.php не найден. Запустите build из MODX-инсталляции или укажите SCRIPTHUB_MODX_CORE_PATH в _build/build.config.php\n");
    exit(1);
}

require_once MODX_CORE_PATH . 'vendor/autoload.php';

// --- 3. Bootstrap MODX ---
$modx = new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$packageName    = 'scripthub';
$packageVersion = SCRIPTHUB_VERSION;
$packageRelease = SCRIPTHUB_RELEASE;

echo "\n=== Building {$packageName} {$packageVersion}-{$packageRelease} ===\n";
echo "Source root: {$sourceRoot}\n";
echo "MODX core:   " . MODX_CORE_PATH . "\n\n";

// Sanity-check: исходники на месте.
$mustExist = [
    $sourceRoot . '/core/components/scripthub/bootstrap.php',
    $sourceRoot . '/core/components/scripthub/elements/plugins/plugin.scripthub.php',
    $sourceRoot . '/core/components/scripthub/model/schema/scripthub.mysql.schema.xml',
    $sourceRoot . '/assets/components/scripthub/connector.php',
    $sourceRoot . '/assets/components/scripthub/mgr/vue-dist/scripthub-admin.min.js',
];
foreach ($mustExist as $required) {
    if (!file_exists($required)) {
        fwrite(STDERR, "ERROR: отсутствует обязательный файл: {$required}\n");
        if (str_contains($required, 'vue-dist')) {
            fwrite(STDERR, "  → сначала соберите Vue: cd assets/components/scripthub/js && npm run build\n");
        }
        exit(1);
    }
}

// --- 4. Package builder ---
$builder = new modPackageBuilder($modx);
$builder->createPackage($packageName, $packageVersion, $packageRelease);
$builder->registerNamespace(
    $packageName,
    false,
    true,
    '{core_path}components/scripthub/',
    '{assets_path}components/scripthub/'
);
echo "[ok] Namespace registered\n";

// --- 5. Category vehicle: Plugin + Events + File/Table resolvers ---
/** @var modPlugin[] $plugins */
$plugins = include $buildRoot . '/data/transport.plugins.php';

$category = $modx->newObject(modCategory::class);
$category->fromArray([
    'id'       => 1,
    'category' => 'scriptHub',
], '', true, true);
$category->addMany($plugins, 'Plugins');

$catAttributes = [
    xPDOTransport::UNIQUE_KEY                => 'category',
    xPDOTransport::PRESERVE_KEYS             => false,
    xPDOTransport::UPDATE_OBJECT             => true,
    xPDOTransport::RELATED_OBJECTS           => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
        'Plugins' => [
            xPDOTransport::PRESERVE_KEYS             => false,
            xPDOTransport::UPDATE_OBJECT             => true,
            xPDOTransport::UNIQUE_KEY                => 'name',
            xPDOTransport::RELATED_OBJECTS           => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => false,
                    xPDOTransport::UNIQUE_KEY    => ['pluginid', 'event'],
                ],
            ],
        ],
    ],
];

$vehicle = $builder->createVehicle($category, $catAttributes);

$vehicle->resolve('file', [
    'source' => $sourceRoot . '/core/components/scripthub/',
    'target' => "return MODX_CORE_PATH . 'components/';",
]);
$vehicle->resolve('file', [
    'source' => $sourceRoot . '/assets/components/scripthub/',
    'target' => "return MODX_ASSETS_PATH . 'components/';",
]);
$vehicle->resolve('php', ['source' => $buildRoot . '/resolvers/tables.resolver.php']);
$vehicle->resolve('php', ['source' => $buildRoot . '/resolvers/upgrade.resolver.php']);

$builder->putVehicle($vehicle);
echo "[ok] Category + " . count($plugins) . " plugin(s) + file/table/upgrade resolvers\n";

// --- 6. System Settings ---
/** @var modSystemSetting[] $settings */
$settings = include $buildRoot . '/data/transport.settings.php';
$settingAttributes = [
    xPDOTransport::UNIQUE_KEY    => 'key',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => false,
];
foreach ($settings as $setting) {
    $sv = $builder->createVehicle($setting, $settingAttributes);
    $builder->putVehicle($sv);
}
echo "[ok] " . count($settings) . " system setting(s)\n";

// --- 7. Menu ---
/** @var modMenu $menu */
$menu = include $buildRoot . '/data/transport.menu.php';
$menuAttributes = [
    xPDOTransport::UNIQUE_KEY    => 'text',
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
];
$mv = $builder->createVehicle($menu, $menuAttributes);
$builder->putVehicle($mv);
echo "[ok] Menu\n";

// --- 8. Package metadata ---
$builder->setPackageAttributes([
    'license'   => file_get_contents($buildRoot . '/docs/license.txt'),
    'readme'    => file_get_contents($buildRoot . '/docs/readme.txt'),
    'changelog' => file_get_contents($buildRoot . '/docs/changelog.txt'),
]);

// --- 9. Pack ---
$success = $builder->pack();

$signature = "{$packageName}-{$packageVersion}-{$packageRelease}";
$artifact  = MODX_CORE_PATH . "packages/{$signature}.transport.zip";

echo "\n=== ", ($success ? 'BUILD OK' : 'BUILD FAILED'), " ===\n";
if ($success && is_file($artifact)) {
    echo "Artifact: {$artifact}\n";
    echo "Size:     " . round(filesize($artifact) / 1024, 1) . " KB\n";
    echo "Signature: {$signature}\n";
    exit(0);
}

fwrite(STDERR, "ERROR: artifact not produced\n");
exit(2);
