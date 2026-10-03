<?php

/**
 * Создаёт / поддерживает таблицу scripthub_services при install / upgrade.
 * При uninstall удаляет таблицу.
 *
 * @var array $options
 * @var \MODX\Revolution\modX|object $object
 */

if (!$object->xpdo) {
    return false;
}

/** @var \MODX\Revolution\modX $modx */
$modx =& $object->xpdo;

$modelPath = $modx->getOption(
    'scripthub.core_path',
    null,
    $modx->getOption('core_path') . 'components/scripthub/'
) . 'model/';

$modx->addPackage('scripthub', $modelPath);
$manager = $modx->getManager();

$serviceClass = \scripthub\ScriptHubService::class;

switch ($options[\xPDO\Transport\xPDOTransport::PACKAGE_ACTION] ?? null) {
    case \xPDO\Transport\xPDOTransport::ACTION_INSTALL:
    case \xPDO\Transport\xPDOTransport::ACTION_UPGRADE:
        $manager->createObjectContainer($serviceClass);
        break;

    case \xPDO\Transport\xPDOTransport::ACTION_UNINSTALL:
        $manager->removeObjectContainer($serviceClass);
        break;
}

return true;
