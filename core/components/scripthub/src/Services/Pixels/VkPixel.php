<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Pixels;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class VkPixel extends AbstractService
{
    public function getKey(): string
    {
        return 'vk-pixel';
    }

    public function getName(): string
    {
        return 'VK Пиксель';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::Pixels;
    }

    public function getIcon(): string
    {
        return 'pi pi-bullseye';
    }

    public function getDescription(): string
    {
        return 'Пиксель ВКонтакте для ретаргетинга и отслеживания конверсий рекламных кампаний VK Ads';
    }

    public function getDocsUrl(): string
    {
        return 'https://ads.vk.com/help/articles/pixel';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'pixel_id',
                'label' => 'ID пикселя',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'VK-RTRG-000000-XXXXX',
                'helpText' => 'Идентификатор пикселя из кабинета VK Рекламы (формат VK-RTRG-XXXXX-XXXXX)',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    public function render(): string
    {
        $pixelId = $this->cfg('pixel_id');
        if (empty($pixelId)) {
            return '';
        }

        $escapedId = htmlspecialchars((string) $pixelId, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!-- VK Pixel (scriptHub) -->
<script>
!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,
t.src="https://vk.com/js/api/openapi.js?169",t.onload=function(){VK.Retargeting.Init("{$escapedId}"),
VK.Retargeting.Hit()},document.head.appendChild(t)}();
</script>
<!-- /VK Pixel -->
HTML;
    }

    public function renderNoscript(): string
    {
        $pixelId = $this->cfg('pixel_id');
        if (empty($pixelId)) {
            return '';
        }
        $escapedId = htmlspecialchars((string) $pixelId, ENT_QUOTES, 'UTF-8');
        return '<noscript><img src="https://vk.com/rtrg?p=' . $escapedId . '" style="position:fixed;left:-999px" alt="" /></noscript>';
    }
}
