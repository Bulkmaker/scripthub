<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\LeadGen\Calltouch;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class Calltouch extends AbstractService
{
    public function getKey(): string
    {
        return 'calltouch';
    }

    public function getName(): string
    {
        return 'Calltouch';
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
        return 'Calltouch -- коллтрекинг, сквозная аналитика и управление лидами';
    }

    public function getDocsUrl(): string
    {
        return 'https://support.calltouch.ru/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'mod_id',
                'label' => 'ID модуля',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'xxxxxxxx',
                'helpText' => 'Идентификатор модуля из личного кабинета Calltouch (mod_id)',
            ],
            [
                'key' => 'site_id',
                'label' => 'ID сайта',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => '12345',
                'helpText' => 'Числовой идентификатор сайта (ct_site_id)',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $modId = $this->cfg('mod_id');
        $siteId = $this->cfg('site_id');
        if (empty($modId) || empty($siteId)) {
            return '';
        }

        $safeModId = $this->jsEncode($this->sanitizeId((string) $modId));
        $safeSiteId = $this->jsEncode($this->sanitizeId((string) $siteId));

        return <<<HTML
<!-- Calltouch (scriptHub) -->
<script>
(function(w,d,n,c){w.CalltouchDataObject=n;w[n]=function(){w[n]["callbacks"].push(arguments)};
if(!w[n]["callbacks"]){w[n]["callbacks"]=[]}w[n]["loaded"]=false;if(typeof c!=="object"){c=[c]}
w[n]["counters"]=c;for(var i=0;i<c.length;i++){p(c[i])}function p(c498c){
var s=d.createElement("script");s.type="text/javascript";s.async=true;
s.src="https://mod.calltouch.ru/init.js?id="+c498c;
var i=d.getElementsByTagName("script")[0];i.parentNode.insertBefore(s,i)}
})(window,document,"ct",{$safeModId});
window.ct('calltracking_params',['phone']);
window.ct_site_id={$safeSiteId};
</script>
<!-- /Calltouch -->
HTML;
    }
}
