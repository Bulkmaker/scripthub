<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Analytics\GoogleAnalytics;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class GoogleAnalytics extends AbstractService
{
    public function getKey(): string
    {
        return 'google-analytics';
    }

    public function getName(): string
    {
        return 'Google Analytics 4';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::Analytics;
    }

    public function getIcon(): string
    {
        return 'pi pi-chart-bar';
    }

    public function getDescription(): string
    {
        return 'Google Analytics 4 (GA4) -- аналитика сайта с расширенным отслеживанием событий';
    }

    public function getDocsUrl(): string
    {
        return 'https://developers.google.com/analytics';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'measurement_id',
                'label' => 'Measurement ID',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'G-XXXXXXXXXX',
                'helpText' => 'Идентификатор потока данных из настроек GA4 (формат G-XXXXXXX)',
            ],
            [
                'key' => 'send_page_view',
                'label' => 'Автоматически отправлять page_view',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Если отключить — gtag не отправит автоматический page_view при загрузке. Расширенное измерение (прокрутка, клики, видео) настраивается в админке GA4, не из кода.',
            ],
            [
                'key' => 'anonymize_ip',
                'label' => 'Анонимизировать IP',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Рекомендуется для соответствия 152-ФЗ и GDPR. Анонимизирует последний октет IPv4 / последние 80 бит IPv6.',
            ],
            [
                'key' => 'allow_ad_personalization_signals',
                'label' => 'Разрешить персонализацию рекламы',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Сигналы для персонализации Google Ads. По умолчанию выключено для GDPR/152-ФЗ.',
            ],
            [
                'key' => 'debug_mode',
                'label' => 'Режим отладки',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Включить debug_mode для отладки событий в DebugView',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    public function render(): string
    {
        $id = $this->cfg('measurement_id');
        if (empty($id)) {
            return '';
        }

        $safeMeasurementId = $this->sanitizeId((string) $id);
        if (empty($safeMeasurementId)) {
            return '';
        }
        $jsMeasurementId = $this->jsEncode($safeMeasurementId);
        $attrMeasurementId = $this->escAttr($safeMeasurementId);

        $configParams = [];
        // send_page_view: false эмиттится только если пользователь явно отключил автоматический page_view
        if (!$this->cfg('send_page_view', true)) {
            $configParams[] = "'send_page_view': false";
        }
        if ($this->cfg('anonymize_ip', true)) {
            $configParams[] = "'anonymize_ip': true";
        }
        if (!$this->cfg('allow_ad_personalization_signals', false)) {
            $configParams[] = "'allow_ad_personalization_signals': false";
        }
        if ($this->cfg('debug_mode', false)) {
            $configParams[] = "'debug_mode': true";
        }

        $configStr = !empty($configParams)
            ? ', {' . implode(', ', $configParams) . '}'
            : '';

        return <<<HTML
<!-- Google Analytics 4 (scriptHub) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$attrMeasurementId}"></script>
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('js',new Date());
gtag('config',{$jsMeasurementId}{$configStr});
</script>
<!-- /Google Analytics 4 -->
HTML;
    }
}
