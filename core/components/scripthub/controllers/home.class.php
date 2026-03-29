<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/index.class.php';

/**
 * scriptHub — Home Manager Controller (CMP page)
 */
class ScriptHubHomeManagerController extends ScriptHubManagerController
{
    protected \RenderRoom\ScriptHub\ScriptHub $scriptHub;

    /**
     * Flag for VueTools check script registration (once per page)
     */
    protected static bool $vueToolsCheckRegistered = false;

    public function initialize(): void
    {
        parent::initialize();

        $this->scriptHub = $this->modx->services->get('scripthub');
    }

    public function getPageTitle(): string
    {
        return 'scriptHub';
    }

    public function getLanguageTopics(): array
    {
        return ['scripthub:default'];
    }

    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function loadCustomCssJs(): void
    {
        $config = $this->scriptHub->getConfig();
        $version = $this->scriptHub->getVersion();

        // CSS
        $cssFile = $config['assetsPath'] . 'css/scripthub.css';
        $this->addCss($config['cssUrl'] . 'scripthub.css?v=' . (file_exists($cssFile) ? filemtime($cssFile) : $version));

        // Vue ES module with VueTools check
        $this->addVueModule($config['assetsUrl'] . 'mgr/vue-dist/scripthub-admin.min.js');

        // Config (siteId for HTTP_MODAUTH — needed before MODx object is available)
        $this->addHtml('<script>
            window.ScriptHub = window.ScriptHub || {};
            ScriptHub.config = ' . json_encode([
                'connectorUrl' => $config['connectorUrl'],
                'assetsUrl'    => $config['assetsUrl'],
                'version'      => $version,
                'siteId'       => $this->modx->user->getUserToken($this->modx->context->get('key')),
            ], JSON_UNESCAPED_SLASHES) . ';
        </script>');
    }

    public function getTemplateFile(): string
    {
        return '';
    }

    public function process(array $scriptProperties = []): string
    {
        return '<div id="scripthub-admin-app" class="vueApp"></div>';
    }

    /**
     * Register Vue ES module with VueTools dependency check
     */
    public function addVueModule(string $src): void
    {
        if (!self::$vueToolsCheckRegistered) {
            $this->registerVueToolsCheck();
            self::$vueToolsCheckRegistered = true;
        }

        $src = $src . '?v=' . filemtime($this->scriptHub->getConfig('assetsPath') . 'mgr/vue-dist/scripthub-admin.min.js');

        $this->modx->regClientStartupHTMLBlock(
            '<script type="module" data-vue-module src="' . $src . '"></script>'
        );
    }

    /**
     * Register inline script for Import Map check
     * If VueTools is not installed — shows MODX alert
     */
    protected function registerVueToolsCheck(): void
    {
        $alertTitle = json_encode(
            $this->modx->lexicon('scripthub_error') ?: 'Error',
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
        );
        $alertMessage = json_encode(
            $this->modx->lexicon('scripthub_vuetools_required')
                ?: 'Для работы scriptHub необходим пакет VueTools. Установите его из Package Manager.',
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
        );

        $script = <<<JS
<script>
(function() {
    var importMap = document.querySelector('script[type="importmap"]');
    var hasVueTools = false;
    if (importMap) {
        try {
            var mapContent = JSON.parse(importMap.textContent);
            hasVueTools = mapContent.imports && mapContent.imports.vue;
        } catch (e) {
            hasVueTools = false;
        }
    }
    if (!hasVueTools) {
        document.querySelectorAll('script[type="module"][data-vue-module]').forEach(function(el) {
            el.remove();
        });
        var title = {$alertTitle};
        var msg = {$alertMessage};
        if (typeof Ext !== 'undefined') {
            Ext.onReady(function() {
                if (typeof MODx !== 'undefined' && MODx.msg) {
                    MODx.msg.alert(title, msg);
                } else {
                    alert(msg);
                }
            });
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    if (typeof MODx !== 'undefined' && MODx.msg) {
                        MODx.msg.alert(title, msg);
                    } else {
                        alert(msg);
                    }
                }, 500);
            });
        }
        window.SCRIPTHUB_VUE_TOOLS_MISSING = true;
    }
})();
</script>
JS;

        $this->modx->regClientStartupHTMLBlock($script);
    }
}
