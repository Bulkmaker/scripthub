<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\LeadGen\SendPulse;

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
        return 'SendPulse — попап-формы, push-уведомления и формы подписки. Для Web Push дополнительно нужно загрузить sw.js и manifest.json в корень сайта.';
    }

    public function getDocsUrl(): string
    {
        return 'https://sendpulse.com/knowledge-base/push-notifications/add-website-send-push-notifications';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'script_url',
                'label' => 'URL скрипта',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'https://web.webpushs.com/js/push/xxxxxxxx_1.js',
                'helpText' => 'Скопируйте полный URL с https:// из ЛК SendPulse. Web Push: web.webpushs.com. Pop-ups: static.sendpulse.com. Subscription forms: static-login.sendpulse.com',
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

        // Allow-list реальных хостов SendPulse для каждого продукта (Web Push,
        // pop-ups, subscription forms). См. https://sendpulse.com/knowledge-base.
        $safeUrl = $this->sanitizeUrl((string) $scriptUrl, [
            'web.webpushs.com',
            'static.sendpulse.com',
            'static-login.sendpulse.com',
            'login.sendpulse.com',
            'cdn.sendpulse.com',
        ]);
        if (empty($safeUrl)) {
            return '';
        }

        $attrUrl = $this->escAttr($safeUrl);

        return <<<HTML
<!-- SendPulse (scriptHub) -->
<script src="{$attrUrl}" async charset="utf-8"></script>
<!-- /SendPulse -->
HTML;
    }
}
