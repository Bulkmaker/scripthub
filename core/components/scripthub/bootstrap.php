<?php

declare(strict_types=1);

/**
 * scriptHub Bootstrap
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

if (defined('SCRIPTHUB_BOOTSTRAPPED')) {
    return;
}
define('SCRIPTHUB_BOOTSTRAPPED', true);

$corePath = $modx->getOption(
    'scripthub.core_path',
    null,
    $modx->getOption('core_path') . 'components/scripthub/'
);

$assetsUrl = $modx->getOption(
    'scripthub.assets_url',
    null,
    $modx->getOption('assets_url') . 'components/scripthub/'
);

// --- Autoloading ---
$autoloader = $corePath . 'vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
} else {
    spl_autoload_register(function (string $class) use ($corePath) {
        $prefix = 'RenderRoom\\ScriptHub\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $file = $corePath . 'src/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
}

// --- xPDO Package (model -> table mapping) ---
$modx->addPackage('scripthub', $corePath . 'model/');

// --- Register scriptHub service in MODX DI container ---
if (!$modx->services->has('scripthub')) {
    $modx->services->add('scripthub', function () use ($modx, $corePath, $assetsUrl) {
        return new \RenderRoom\ScriptHub\ScriptHub($modx, [
            'corePath' => $corePath,
            'assetsUrl' => $assetsUrl,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'src/Processors/',
            'connectorUrl' => $assetsUrl . 'connector.php',
        ]);
    });
}
