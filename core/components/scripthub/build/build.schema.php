<?php

declare(strict_types=1);

/**
 * Build xPDO model from schema
 *
 * Usage: docker compose exec -u www-data app php core/components/scripthub/build/build.schema.php
 */

require_once dirname(__FILE__, 5) . '/config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';

$modx = new MODX\Revolution\modX();
$modx->initialize('web');
$modx->setLogLevel(\xPDO\xPDO::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$manager = $modx->getManager();
$generator = $manager->getGenerator();

$schemaFile = dirname(__FILE__, 2) . '/model/schema/scripthub.mysql.schema.xml';
$modelPath = dirname(__FILE__, 2) . '/model/';

echo "Parsing schema: {$schemaFile}\n";
echo "Output path: {$modelPath}\n";

$generator->parseSchema($schemaFile, $modelPath);

echo "\nModel generated successfully.\n";
echo "Now create the table by running:\n";
echo "  \$modx->addPackage('scripthub', MODX_CORE_PATH . 'components/scripthub/model/');\n";
echo "  \$manager->createObjectContainer('ScriptHubService');\n";
