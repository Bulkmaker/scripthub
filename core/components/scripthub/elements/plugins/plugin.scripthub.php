<?php
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

        // Inject into <head> — case-insensitive, only first occurrence
        $headScripts = $renderer->renderForPosition(
            \RenderRoom\ScriptHub\Services\InjectionPosition::Head
        );
        if ($headScripts) {
            $output = preg_replace(
                '~</head>~i',
                $headScripts . "\n</head>",
                $output,
                1
            );
        }

        // Inject before </body> — case-insensitive, only first occurrence
        $bodyEndScripts = $renderer->renderForPosition(
            \RenderRoom\ScriptHub\Services\InjectionPosition::BodyEnd
        );
        if ($bodyEndScripts) {
            $output = preg_replace(
                '~</body>~i',
                $bodyEndScripts . "\n</body>",
                $output,
                1
            );
        }

        // Inject after <body...>: noscript first, then afterBodyOpen scripts.
        // preg_replace_callback avoids backreference injection if $afterBody
        // contains literal "$1", "\1", etc.
        $afterBodyOpen = $renderer->renderForPosition(
            \RenderRoom\ScriptHub\Services\InjectionPosition::AfterBodyOpen
        );
        $noscript = $renderer->renderNoscriptAll();
        $afterBody = trim($noscript . "\n" . $afterBodyOpen);
        if ($afterBody) {
            $output = preg_replace_callback(
                '/<body[^>]*>/i',
                function ($m) use ($afterBody) {
                    return $m[0] . "\n" . $afterBody;
                },
                $output,
                1
            );
        }
        break;
}