<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Chats\JivoSite;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class JivoSite extends AbstractService
{
    public function getKey(): string
    {
        return 'jivosite';
    }

    public function getName(): string
    {
        return 'JivoSite';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::Chats;
    }

    public function getIcon(): string
    {
        return 'pi pi-comments';
    }

    public function getDescription(): string
    {
        return 'JivoSite -- онлайн-чат, обратный звонок и мессенджеры в одном виджете';
    }

    public function getDocsUrl(): string
    {
        return 'https://www.jivo.ru/help/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'widget_id',
                'label' => 'ID виджета',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'XXXXXXXXXXXX',
                'helpText' => 'Идентификатор виджета из Настройки > Каналы связи > Установка на сайт',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $widgetId = $this->cfg('widget_id');
        if (empty($widgetId)) {
            return '';
        }

        $safeId = $this->escAttr($this->sanitizeId((string) $widgetId));

        return <<<HTML
<!-- JivoSite (scriptHub) -->
<script src="//code.jivosite.com/widget/{$safeId}" async></script>
<!-- /JivoSite -->
HTML;
    }
}
