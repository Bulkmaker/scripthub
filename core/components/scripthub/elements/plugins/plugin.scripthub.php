<?php
declare(strict_types=1);

/**
 * scriptHub Plugin
 *
 * Events: OnMODXInit, OnWebPagePrerender
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $scriptProperties
 */

$corePath = $modx->getOption(
    'scripthub.core_path',
    null,
    $modx->getOption('core_path') . 'components/scripthub/'
);

switch ($modx->event->name) {
    case 'OnMODXInit':
        if (file_exists($corePath . 'bootstrap.php')) {
            require_once $corePath . 'bootstrap.php';
        }
        break;

    case 'OnWebPagePrerender':
        if ($modx->context->key === 'mgr') {
            break;
        }

        if (!$modx->resource || !$modx->services->has('scripthub')) {
            break;
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $modx->services->get('scripthub');
        $renderer = new \RenderRoom\ScriptHub\Renderer\ScriptRenderer($scriptHub);

        $output = &$modx->resource->_output;

        // Inject into <head>
        $headScripts = $renderer->renderForPosition(
            \RenderRoom\ScriptHub\Services\InjectionPosition::Head
        );
        if ($headScripts) {
            $output = str_replace('</head>', $headScripts . "\n</head>", $output);
        }

        // Inject before </body>
        $bodyEndScripts = $renderer->renderForPosition(
            \RenderRoom\ScriptHub\Services\InjectionPosition::BodyEnd
        );
        if ($bodyEndScripts) {
            $output = str_replace('</body>', $bodyEndScripts . "\n</body>", $output);
        }

        // Inject after <body...>: noscript first, then afterBodyOpen scripts
        // Combined into single preg_replace to maintain correct order
        $afterBodyOpen = $renderer->renderForPosition(
            \RenderRoom\ScriptHub\Services\InjectionPosition::AfterBodyOpen
        );
        $noscript = $renderer->renderNoscriptAll();
        $afterBody = trim($noscript . "\n" . $afterBodyOpen);
        if ($afterBody) {
            $output = preg_replace(
                '/(<body[^>]*>)/i',
                '$1' . "\n" . $afterBody,
                $output
            );
        }
        break;
}