<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\LeadGen;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class SendPulse extends AbstractService
{
    public function getKey(): string
    {
        return 'sendpulse';
    }

    public function getName(): string
    {
        return 'SendPulse';
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
        return 'SendPulse -- попап-формы, push-уведомления и чат-боты для сбора лидов';
    }

    public function getDocsUrl(): string
    {
        return 'https://sendpulse.com/knowledge-base/pop-ups-subscription-forms';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'script_url',
                'label' => 'URL скрипта',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'https://cdn.sendpulse.com/js/push/xxxxxxxx-xxxx.js',
                'helpText' => 'Полный URL скрипта из раздела Pop-ups > Установка на сайт в SendPulse',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $scriptUrl = $this->cfg('script_url');
        if (empty($scriptUrl)) {
            return '';
        }

        $escapedUrl = htmlspecialchars((string) $scriptUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!-- SendPulse (scriptHub) -->
<script src="{$escapedUrl}" async charset="utf-8"></script>
<!-- /SendPulse -->
HTML;
    }
}
