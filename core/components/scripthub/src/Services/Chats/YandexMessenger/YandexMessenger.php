<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Chats\YandexMessenger;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class YandexMessenger extends AbstractService
{
    public function getKey(): string
    {
        return 'yandex-messenger';
    }

    public function getName(): string
    {
        return 'Яндекс Мессенджер';
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
        return 'Чаты для бизнеса от Яндекса — виджет онлайн-консультанта на сайте, единый GUID чата из личного кабинета';
    }

    public function getDocsUrl(): string
    {
        return 'https://yandex.ru/support/business-chats/widget.html';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'guid',
                'label' => 'GUID чата',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'helpText' => 'Идентификатор чата из ЛК «Чаты для бизнеса» Яндекса (Настройки чата → Виджет → GUID)',
            ],
            [
                'key' => 'theme',
                'label' => 'Тема оформления',
                'type' => FieldType::Text->value,
                'default' => 'light',
                'placeholder' => 'light',
                'helpText' => 'Допустимые значения: light, dark',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $guid = (string) $this->cfg('guid');
        if (empty($guid)) {
            return '';
        }
        // Принимаем UUID с дефисами — sanitizeId() это разрешает.
        $cleanGuid = $this->sanitizeId($guid);
        if ($cleanGuid === '') {
            return '';
        }
        $safeGuid = $this->jsEncode($cleanGuid);

        $themeOption = '';
        $theme = strtolower(trim((string) $this->cfg('theme', '')));
        if (in_array($theme, ['light', 'dark'], true)) {
            $safeTheme = $this->jsEncode($theme);
            $themeOption = ",theme:{$safeTheme}";
        }

        return <<<HTML
<!-- Yandex Chat Widget (scriptHub) -->
<script>
window.yandexChatWidgetCallback=function(){new YandexChatWidget({guid:{$safeGuid}{$themeOption}});};
(function(){var s=document.createElement('script');s.async=true;
s.src='https://yastatic.net/s3/chat/widget.js';
document.head.appendChild(s);})();
</script>
<!-- /Yandex Chat Widget -->
HTML;
    }
}
