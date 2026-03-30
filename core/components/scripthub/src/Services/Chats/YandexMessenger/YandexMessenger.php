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
        return 'Чат для бизнеса от Яндекса -- виджет онлайн-консультанта на сайте';
    }

    public function getDocsUrl(): string
    {
        return 'https://yandex.ru/chat/business';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'org_id',
                'label' => 'ID организации',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'helpText' => 'Идентификатор организации из Яндекс Мессенджера для бизнеса',
            ],
            [
                'key' => 'chat_id',
                'label' => 'ID чата',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'helpText' => 'Идентификатор чат-канала',
            ],
            [
                'key' => 'color',
                'label' => 'Цвет виджета',
                'type' => FieldType::Text->value,
                'default' => '',
                'placeholder' => '#FFD700',
                'helpText' => 'HEX-цвет кнопки и заголовка чата (оставьте пустым для цвета по умолчанию)',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $orgId = $this->cfg('org_id');
        $chatId = $this->cfg('chat_id');
        if (empty($orgId) || empty($chatId)) {
            return '';
        }

        $safeOrgId = $this->jsEncode($this->sanitizeId((string) $orgId));
        $safeChatId = $this->jsEncode($this->sanitizeId((string) $chatId));

        $colorOption = '';
        $color = $this->cfg('color', '');
        if (!empty($color)) {
            $safeColor = $this->jsEncode((string) $color);
            $colorOption = ",color:{$safeColor}";
        }

        return <<<HTML
<!-- Yandex Messenger (scriptHub) -->
<script>
(function(){var w=window,d=document,s=d.createElement('script');
s.src='https://chat.s3.yandex.net/widget.js';s.async=true;
s.onload=function(){
Ya.Chat.Widget.open({serviceId:{$safeOrgId},chatId:{$safeChatId}{$colorOption}});
};d.body.appendChild(s);})();
</script>
<!-- /Yandex Messenger -->
HTML;
    }
}
