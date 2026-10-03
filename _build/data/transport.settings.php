<?php

/**
 * @var \MODX\Revolution\modX $modx
 */

$settings = [];

$tmp = [
    'scripthub.core_path' => [
        'value' => '{core_path}components/scripthub/',
        'xtype' => 'textfield',
        'area'  => 'paths',
    ],
    'scripthub.assets_url' => [
        'value' => '{assets_url}components/scripthub/',
        'xtype' => 'textfield',
        'area'  => 'paths',
    ],
];

foreach ($tmp as $key => $data) {
    $setting = $modx->newObject(\MODX\Revolution\modSystemSetting::class);
    $setting->fromArray([
        'key'       => $key,
        'value'     => $data['value'],
        'xtype'     => $data['xtype'],
        'namespace' => 'scripthub',
        'area'      => $data['area'],
    ], '', true, true);
    $settings[$key] = $setting;
}

unset($tmp);
return $settings;
