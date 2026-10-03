<?php

/**
 * Upgrade-резолвер.
 *
 * До 1.0.0 у таблицы scripthub_services не было поля `added` или оно
 * добавлялось без миграции существующих строк. Этот резолвер при апгрейде
 * помечает все ранее enabled-сервисы как added=1, чтобы они продолжали
 * инжектиться И были видны в админ-дашборде (фронт-инъекция требует
 * isEnabled() && isAdded(), см. ServiceRegistry::getEnabled).
 *
 * @var array $options
 * @var \MODX\Revolution\modX|object $object
 */

if (!$object->xpdo) {
    return false;
}

if (($options[\xPDO\Transport\xPDOTransport::PACKAGE_ACTION] ?? null) !== \xPDO\Transport\xPDOTransport::ACTION_UPGRADE) {
    return true;
}

/** @var \MODX\Revolution\modX $modx */
$modx =& $object->xpdo;

$modelPath = $modx->getOption(
    'scripthub.core_path',
    null,
    $modx->getOption('core_path') . 'components/scripthub/'
) . 'model/';
$modx->addPackage('scripthub', $modelPath);

$table = $modx->getTableName(\scripthub\ScriptHubService::class);
if (!$table) {
    return true;
}

$sql = "UPDATE {$table} SET `added` = 1 WHERE `enabled` = 1 AND `added` = 0";
$affected = (int) $modx->exec($sql);

$modx->log(
    \MODX\Revolution\modX::LOG_LEVEL_INFO,
    "[scriptHub] upgrade resolver: migrated {$affected} previously-enabled rows to added=1"
);

return true;
