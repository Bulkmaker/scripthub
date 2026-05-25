<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Analytics\Matomo;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class Matomo extends AbstractService
{
    public function getKey(): string
    {
        return 'matomo';
    }

    public function getName(): string
    {
        return 'Matomo';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::Analytics;
    }

    public function getIcon(): string
    {
        return 'pi pi-chart-pie';
    }

    public function getDescription(): string
    {
        return 'Matomo (self-hosted) -- аналитика с полным контролем данных на вашем сервере';
    }

    public function getDocsUrl(): string
    {
        return 'https://matomo.org/guides/tracking-javascript-guide/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'site_url',
                'label' => 'URL сервера Matomo',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'https://analytics.example.com',
                'helpText' => 'Адрес вашего сервера Matomo без завершающего слэша',
            ],
            [
                'key' => 'site_id',
                'label' => 'ID сайта',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => '1',
                'helpText' => 'Идентификатор сайта в Matomo (Настройки > Сайты > Управление)',
            ],
            [
                'key' => 'track_links',
                'label' => 'Отслеживание ссылок и загрузок',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Автоматическое отслеживание кликов по внешним ссылкам и загрузок файлов',
            ],
            [
                'key' => 'disable_cookies',
                'label' => 'Без cookie',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Отключить использование cookie для соответствия GDPR',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    /**
     * Parse user-supplied site_url and rebuild from scheme+host+port only.
     * Drops path/query/fragment/userinfo, blocks non-http(s) schemes.
     * Returns '' on invalid input.
     */
    protected function safeSiteUrl(): string
    {
        $raw = rtrim((string) $this->cfg('site_url'), '/');
        $parsed = @parse_url($raw);
        if (!is_array($parsed) || empty($parsed['scheme']) || empty($parsed['host'])) {
            return '';
        }
        if (!in_array($parsed['scheme'], ['http', 'https'], true)) {
            return '';
        }
        $hostOk = filter_var($parsed['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)
            || filter_var($parsed['host'], FILTER_VALIDATE_IP);
        if (!$hostOk) {
            return '';
        }
        $url = $parsed['scheme'] . '://' . $parsed['host'];
        if (!empty($parsed['port'])) {
            $url .= ':' . (int) $parsed['port'];
        }
        return $url;
    }

    public function render(): string
    {
        $siteUrl = $this->safeSiteUrl();
        $siteId = (int) $this->cfg('site_id');
        if ($siteUrl === '' || $siteId < 1) {
            return '';
        }

        $jsUrl = $this->jsEncode($siteUrl);
        $jsSiteId = $this->jsEncode((string) $siteId);

        // disableCookies должен идти ДО trackPageView — иначе первый pageview
        // уйдёт с cookies (см. developer.matomo.org/api-reference/tracking-javascript).
        $preTrack = '';
        if ($this->cfg('disable_cookies', false)) {
            $preTrack .= "_paq.push(['disableCookies']);\n";
        }
        $postTrack = '';
        if ($this->cfg('track_links', true)) {
            $postTrack .= "\n_paq.push(['enableLinkTracking']);";
        }

        return <<<HTML
<!-- Matomo (scriptHub) -->
<script>
var _paq=window._paq=window._paq||[];
{$preTrack}_paq.push(['trackPageView']);{$postTrack}
(function(){var u={$jsUrl}+"/";
_paq.push(['setTrackerUrl',u+'matomo.php']);
_paq.push(['setSiteId',{$jsSiteId}]);
var d=document,g=d.createElement('script'),s=d.getElementsByTagName('script')[0];
g.async=true;g.src=u+'matomo.js';s.parentNode.insertBefore(g,s);
})();
</script>
<!-- /Matomo -->
HTML;
    }

    public function renderNoscript(): string
    {
        $siteUrl = $this->safeSiteUrl();
        $siteId = (int) $this->cfg('site_id');
        if ($siteUrl === '' || $siteId < 1) {
            return '';
        }

        $attrUrl = $this->escAttr($siteUrl);

        return '<noscript><p><img src="' . $attrUrl . '/matomo.php?idsite=' . $siteId . '&amp;rec=1" style="border:0" alt="" /></p></noscript>';
    }
}
