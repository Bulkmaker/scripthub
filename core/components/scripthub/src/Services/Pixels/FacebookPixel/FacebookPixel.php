<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Pixels\FacebookPixel;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class FacebookPixel extends AbstractService
{
    public function getKey(): string
    {
        return 'facebook-pixel';
    }

    public function getName(): string
    {
        return 'Meta Pixel (Facebook)';
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
        return 'Meta Pixel (ранее Facebook Pixel) для отслеживания конверсий и создания аудиторий ретаргетинга';
    }

    public function getDocsUrl(): string
    {
        return 'https://developers.facebook.com/docs/meta-pixel';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'pixel_id',
                'label' => 'Pixel ID',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => '123456789012345',
                'helpText' => 'Идентификатор пикселя из Meta Events Manager',
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

        $cleanId = $this->sanitizeId((string) $pixelId);
        if ($cleanId === '') {
            return '';
        }
        $safeId = $this->jsEncode($cleanId);

        return <<<HTML
<!-- Meta Pixel (scriptHub) -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init',{$safeId});
fbq('track','PageView');
</script>
<!-- /Meta Pixel -->
HTML;
    }

    public function renderNoscript(): string
    {
        $pixelId = $this->cfg('pixel_id');
        if (empty($pixelId)) {
            return '';
        }
        $cleanId = $this->sanitizeId((string) $pixelId);
        if ($cleanId === '') {
            return '';
        }
        $safeId = $this->escAttr($cleanId);
        return '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' . $safeId . '&ev=PageView&noscript=1" /></noscript>';
    }
}
