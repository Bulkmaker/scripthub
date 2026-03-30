<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Chats\CarrotQuest;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class CarrotQuest extends AbstractService
{
    public function getKey(): string
    {
        return 'carrotquest';
    }

    public function getName(): string
    {
        return 'Carrot Quest';
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
        return 'Carrot Quest -- чат-бот, онлайн-чат, email-рассылки и аналитика пользователей';
    }

    public function getDocsUrl(): string
    {
        return 'https://www.carrotquest.io/docs/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'api_key',
                'label' => 'API-ключ приложения',
                'type' => FieldType::Password->value,
                'required' => true,
                'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'helpText' => 'API-ключ из Настройки > Установка Carrot Quest на сайт',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $apiKey = $this->cfg('api_key');
        if (empty($apiKey)) {
            return '';
        }

        $safeKey = $this->jsEncode($this->sanitizeId((string) $apiKey));

        return <<<HTML
<!-- Carrot Quest (scriptHub) -->
<script>
!function(){function t(t,e){return function(){window.carrotquestasync.push(t,arguments)}}
if("undefined"==typeof carrotquest){var e=document.createElement("script");
e.type="text/javascript",e.async=!0,e.src="//cdn.carrotquest.app/api.min.js",
document.getElementsByTagName("head")[0].appendChild(e),window.carrotquest={},
window.carrotquestasync=[],carrotquest.settings={};for(var n=["connect","track","identify",
"auth","onReady","addCallback","removeCallback","trackMessageInteraction"],a=0;a<n.length;a++)
carrotquest[n[a]]=t(n[a])}}();
carrotquest.connect({$safeKey});
</script>
<!-- /Carrot Quest -->
HTML;
    }
}
