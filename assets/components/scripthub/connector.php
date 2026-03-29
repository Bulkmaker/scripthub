<?php

declare(strict_types=1);

require_once dirname(__FILE__, 4) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

$modx->lexicon->load('scripthub:default');

$corePath = $modx->getOption(
    'scripthub.core_path',
    null,
    $modx->getOption('core_path') . 'components/scripthub/'
);

require_once $corePath . 'bootstrap.php';

/** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
$scriptHub = $modx->services->get('scripthub');

$modx->request->handleRequest([
    'processors_path' => $scriptHub->getConfig('processorsPath'),
    'location' => '',
]);
