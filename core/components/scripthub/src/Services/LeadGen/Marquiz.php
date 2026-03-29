<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\LeadGen;

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

        $safeId = $this->jsEncode($this->sanitizeId((string) $quizId));
        $embedType = $this->cfg('embed_type', 'popup');

        $initCode = match ($embedType) {
            'button' => "Marquiz.showButton({id:{$safeId}});",
            'inline' => "Marquiz.inline({id:{$safeId},container:'#marquiz-container'});",
            default => "Marquiz.showPopup({id:{$safeId}});",
        };

        return <<<HTML
<!-- Marquiz (scriptHub) -->
<script>
(function(t,p){window.Marquiz?Marquiz.add([t,p]):document.addEventListener('marquizLoaded',function(){Marquiz.add([t,p])})})
('accounts',{id:{$safeId},autoOpen:3,autoOpenFreq:'once'});
(function(){var s=document.createElement('script');s.type='text/javascript';s.async=true;
s.src='//script.marquiz.io/v2.js';var x=document.getElementsByTagName('script')[0];
x.parentNode.insertBefore(s,x);})();
document.addEventListener('DOMContentLoaded',function(){{$initCode}});
</script>
<!-- /Marquiz -->
HTML;
    }
}
