<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Analytics\YandexMetrika;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class YandexMetrika extends AbstractService
{
    public function getKey(): string
    {
        return 'yandex-metrika';
    }

    public function getName(): string
    {
        return 'Яндекс Метрика';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::Analytics;
    }

    public function getIcon(): string
    {
        return 'pi pi-chart-line';
    }

    public function getDescription(): string
    {
        return 'Счётчик Яндекс Метрики с поддержкой Вебвизора, карты кликов и электронной коммерции';
    }

    public function getDocsUrl(): string
    {
        return 'https://yandex.ru/support/metrica/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'counter_id',
                'label' => 'ID счётчика',
                'type' => FieldType::Number->value,
                'required' => true,
                'placeholder' => '12345678',
                'helpText' => 'Номер счётчика из настроек Яндекс Метрики',
            ],
            [
                'key' => 'webvisor',
                'label' => 'Вебвизор',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Запись действий посетителей на сайте',
            ],
            [
                'key' => 'clickmap',
                'label' => 'Карта кликов',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Сбор данных для карты кликов',
            ],
            [
                'key' => 'trackLinks',
                'label' => 'Отслеживание ссылок',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Отслеживание переходов по внешним ссылкам и загрузок файлов',
            ],
            [
                'key' => 'accurateTrackBounce',
                'label' => 'Точный показатель отказов',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Более точное определение отказов (15 секунд)',
            ],
            [
                'key' => 'trackHash',
                'label' => 'Отслеживание хеша в URL',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Отслеживание изменения хеша в адресной строке',
            ],
            [
                'key' => 'ecommerce',
                'label' => 'Электронная коммерция',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Передача данных о покупках в Метрику',
            ],
            [
                'key' => 'ecommerce_container',
                'label' => 'Контейнер данных e-commerce',
                'type' => FieldType::Text->value,
                'default' => 'dataLayer',
                'helpText' => 'Имя JavaScript-контейнера для e-commerce данных',
                'showIf' => ['ecommerce', true],
            ],
            [
                'key' => 'childIframe',
                'label' => 'Отслеживание в iframe',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Передача данных из дочернего iframe',
            ],
            [
                'key' => 'defer',
                'label' => 'Отложенная загрузка (defer)',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Отложить загрузку скрипта для ускорения страницы',
            ],
            [
                'key' => 'self_hosted',
                'label' => 'Хостинг скрипта на своём сервере',
                'type' => FieldType::Toggle->value,
                'default' => false,
                'helpText' => 'Скачать tag.js на ваш сервер. Данные по-прежнему отправляются в Яндекс',
            ],
            [
                'key' => 'noscript',
                'label' => 'Добавить noscript-тег',
                'type' => FieldType::Toggle->value,
                'default' => true,
                'helpText' => 'Учёт посетителей без JavaScript через тег <noscript>',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    public function render(): string
    {
        $id = $this->cfg('counter_id');
        if (empty($id)) {
            return '';
        }

        $assetsUrl = $this->modx->getOption(
            'scripthub.assets_url',
            null,
            $this->modx->getOption('assets_url') . 'components/scripthub/'
        );

        $scriptUrl = $this->cfg('self_hosted', false)
            ? $assetsUrl . 'vendor/tag.js'
            : 'https://mc.yandex.ru/metrika/tag.js';

        $options = [];
        if ($this->cfg('clickmap', true)) {
            $options['clickmap'] = true;
        }
        if ($this->cfg('trackLinks', true)) {
            $options['trackLinks'] = true;
        }
        if ($this->cfg('accurateTrackBounce', true)) {
            $options['accurateTrackBounce'] = true;
        }
        if ($this->cfg('webvisor', true)) {
            $options['webvisor'] = true;
        }
        if ($this->cfg('trackHash', false)) {
            $options['trackHash'] = true;
        }
        if ($this->cfg('ecommerce', false)) {
            $options['ecommerce'] = $this->cfg('ecommerce_container', 'dataLayer');
        }
        if ($this->cfg('childIframe', false)) {
            $options['childIframe'] = true;
        }
        if ($this->cfg('defer', false)) {
            $options['defer'] = true;
        }

        $cleanCounterId = $this->sanitizeId((string) $id);
        if ($cleanCounterId === '') {
            return '';
        }
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $safeCounterId = $this->jsEncode($cleanCounterId);
        $safeScriptUrl = $this->jsEncode($scriptUrl);

        return <<<HTML
<!-- Yandex.Metrika counter (scriptHub) -->
<script>
(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
m[i].l=1*new Date();
for(var j=0;j<document.scripts.length;j++){if(document.scripts[j].src===r){return}}
k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
(window,document,"script",{$safeScriptUrl},"ym");
ym({$safeCounterId},"init",{$optionsJson});
</script>
<!-- /Yandex.Metrika counter -->
HTML;
    }

    public function renderNoscript(): string
    {
        $id = $this->cfg('counter_id');
        if (empty($id) || !$this->cfg('noscript', true)) {
            return '';
        }

        $cleanCounterId = $this->sanitizeId((string) $id);
        if ($cleanCounterId === '') {
            return '';
        }
        $safeCounterId = $this->escAttr($cleanCounterId);
        return '<noscript><div><img src="https://mc.yandex.ru/watch/' . $safeCounterId . '" style="position:absolute;left:-9999px" alt="" /></div></noscript>';
    }
}
