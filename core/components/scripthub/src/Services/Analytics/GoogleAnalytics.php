<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Analytics;

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
                'key' => 'enhanced_measurement',
                'label' => 'Расширенная статистика',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Автоматическое отслеживание прокрутки, кликов по внешним ссылкам, поиска на сайте и видео',
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

        $measurementId = htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8');

        $configParams = [];
        if (!$this->cfg('enhanced_measurement', true)) {
            $configParams[] = "'send_page_view': false";
        }
        if ($this->cfg('debug_mode', false)) {
            $configParams[] = "'debug_mode': true";
        }

        $configStr = !empty($configParams)
            ? ', {' . implode(', ', $configParams) . '}'
            : '';

        return <<<HTML
<!-- Google Analytics 4 (scriptHub) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$measurementId}"></script>
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}
gtag('js',new Date());
gtag('config','{$measurementId}'{$configStr});
</script>
<!-- /Google Analytics 4 -->
HTML;
    }
}
