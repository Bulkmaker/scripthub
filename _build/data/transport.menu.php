<?php

/**
 * @var \MODX\Revolution\modX $modx
 */

$menu = $modx->newObject(\MODX\Revolution\modMenu::class);
$menu->fromArray([
    'text'        => 'scripthub',
    'parent'      => 'components',
    'action'      => 'home',
    'description' => 'scripthub_menu_desc',
    'namespace'   => 'scripthub',
    'menuindex'   => 0,
    'icon'        => '',
    'params'      => '',
    'handler'     => '',
    'permissions' => '',
], '', true, true);

return $menu;
