<?php

/**
 * @var \MODX\Revolution\modX $modx
 */

$plugins = [];

$plugin = $modx->newObject(\MODX\Revolution\modPlugin::class);
$plugin->fromArray([
    'id'          => 1,
    'name'        => 'scriptHub',
    'description' => 'scriptHub — управление внешними скриптами',
    'plugincode'  => '',
    'static'      => true,
    'static_file' => 'core/components/scripthub/elements/plugins/plugin.scripthub.php',
    'disabled'    => false,
    'category'    => 0,
], '', true, true);

$events = [];
foreach (['OnMODXInit', 'OnWebPagePrerender'] as $eventName) {
    $event = $modx->newObject(\MODX\Revolution\modPluginEvent::class);
    $event->fromArray([
        'event'       => $eventName,
        'priority'    => 0,
        'propertyset' => 0,
    ], '', true, true);
    $events[$eventName] = $event;
}
$plugin->addMany($events, 'PluginEvents');

$plugins[] = $plugin;
unset($events);
return $plugins;
