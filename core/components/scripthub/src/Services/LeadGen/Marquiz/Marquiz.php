<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\LeadGen\Marquiz;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class Marquiz extends AbstractService
{
    public function getKey(): string
    {
        return 'marquiz';
    }

    public function getName(): string
    {
        return 'Marquiz';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::LeadGen;
    }

    public function getIcon(): string
    {
        return 'pi pi-megaphone';
    }

    public function getDescription(): string
    {
        return 'Marquiz -- конструктор квизов для лидогенерации, встроенных в сайт или в виде попапа';
    }

    public function getDocsUrl(): string
    {
        return 'https://help.marquiz.ru/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'quiz_id',
                'label' => 'ID квиза',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => '60f1a2b3c4d5e6f7a8b9c0d1',
                'helpText' => 'Идентификатор квиза из личного кабинета Marquiz',
            ],
            [
                'key' => 'embed_type',
                'label' => 'Тип встраивания',
                'type' => FieldType::Select->value,
                'default' => 'popup',
                'options' => [
                    ['value' => 'popup', 'label' => 'Попап (всплывающее окно)'],
                    ['value' => 'button', 'label' => 'Кнопка с попапом'],
                    ['value' => 'inline', 'label' => 'Встроенный на страницу'],
                ],
                'helpText' => 'Способ отображения квиза на сайте',
            ],
            [
                'key' => 'auto_open_delay',
                'label' => 'Авто-открытие попапа (сек)',
                'type' => FieldType::Text->value,
                'default' => '3',
                'placeholder' => '3',
                'helpText' => 'Через сколько секунд автоматически показать попап (только для типа «попап»). 0 — не открывать автоматически.',
            ],
            [
                'key' => 'container_selector',
                'label' => 'CSS-селектор контейнера',
                'type' => FieldType::Text->value,
                'default' => '#marquiz-container',
                'placeholder' => '#marquiz-container',
                'helpText' => 'Для типов «inline» и «кнопка». На странице должен присутствовать элемент с этим селектором — иначе виджет молча не отрендерится.',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $quizId = $this->cfg('quiz_id');
        if (empty($quizId)) {
            return '';
        }

        $cleanId = $this->sanitizeId((string) $quizId);
        if ($cleanId === '') {
            return '';
        }
        $safeId = $this->jsEncode($cleanId);

        $embedType = $this->cfg('embed_type', 'popup');
        $containerSelector = (string) $this->cfg('container_selector', '#marquiz-container');
        $safeContainer = $this->jsEncode($containerSelector);

        // accounts-init — без autoOpen для button/inline, чтобы не было «двойного показа».
        // Для popup autoOpen берётся из настройки.
        if ($embedType === 'popup') {
            $autoOpenDelay = max(0, (int) $this->cfg('auto_open_delay', 3));
            $accountsConfig = $autoOpenDelay > 0
                ? "{id:{$safeId},autoOpen:{$autoOpenDelay},autoOpenFreq:'once'}"
                : "{id:{$safeId}}";
            $extraInit = '';
        } elseif ($embedType === 'button') {
            $accountsConfig = "{id:{$safeId}}";
            $extraInit = "\ndocument.addEventListener('DOMContentLoaded',function(){Marquiz.showButton({id:{$safeId},container:{$safeContainer}});});";
        } else { // inline
            $accountsConfig = "{id:{$safeId}}";
            $extraInit = "\ndocument.addEventListener('DOMContentLoaded',function(){Marquiz.inline({id:{$safeId},container:{$safeContainer}});});";
        }

        return <<<HTML
<!-- Marquiz (scriptHub) -->
<script>
(function(t,p){window.Marquiz?Marquiz.add([t,p]):document.addEventListener('marquizLoaded',function(){Marquiz.add([t,p])})})
('accounts',{$accountsConfig});
(function(){var s=document.createElement('script');s.type='text/javascript';s.async=true;
s.src='//script.marquiz.io/v2.js';var x=document.getElementsByTagName('script')[0];
x.parentNode.insertBefore(s,x);})();{$extraInit}
</script>
<!-- /Marquiz -->
HTML;
    }
}
