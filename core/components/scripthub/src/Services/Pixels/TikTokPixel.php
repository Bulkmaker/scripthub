<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Pixels;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class TikTokPixel extends AbstractService
{
    public function getKey(): string
    {
        return 'tiktok-pixel';
    }

    public function getName(): string
    {
        return 'TikTok Pixel';
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
        return 'TikTok Pixel для отслеживания конверсий и оптимизации рекламных кампаний в TikTok Ads';
    }

    public function getDocsUrl(): string
    {
        return 'https://ads.tiktok.com/help/article/get-started-pixel';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'pixel_id',
                'label' => 'Pixel ID',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'CXXXXXXXXXXXXXXXXX',
                'helpText' => 'Идентификатор пикселя из TikTok Ads Manager',
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
<!-- TikTok Pixel (scriptHub) -->
<script>
!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=
["page","track","identify","instances","debug","on","off","once","ready","alias","group",
"enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],
ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);
return e};ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;
ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,
ttq._o=ttq._o||{},ttq._o[e]=n||{};var a=document.createElement("script");
a.type="text/javascript",a.async=!0,a.src=r+"?sdkid="+e+"&lib="+t;
var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(a,s)};
ttq.load('{$escapedId}');
ttq.page();
}(window,document,'ttq');
</script>
<!-- /TikTok Pixel -->
HTML;
    }
}
