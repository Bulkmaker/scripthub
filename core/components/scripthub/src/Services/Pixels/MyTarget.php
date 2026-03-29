<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Pixels;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class MyTarget extends AbstractService
{
    public function getKey(): string
    {
        return 'mytarget';
    }

    public function getName(): string
    {
        return 'MyTarget (VK Реклама)';
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
        return 'Счётчик Top.Mail.Ru (MyTarget) для аудиторий и конверсий в VK Рекламе';
    }

    public function getDocsUrl(): string
    {
        return 'https://target.my.com/help/advertisers/countertag/ru';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'counter_id',
                'label' => 'ID счётчика',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => '1234567',
                'helpText' => 'Идентификатор счётчика Top.Mail.Ru из кабинета MyTarget',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    public function render(): string
    {
        $counterId = $this->cfg('counter_id');
        if (empty($counterId)) {
            return '';
        }

        $safeId = $this->jsEncode($this->sanitizeId((string) $counterId));

        return <<<HTML
<!-- Top.Mail.Ru / MyTarget (scriptHub) -->
<script>
var _tmr=window._tmr||(window._tmr=[]);
_tmr.push({id:{$safeId},type:"pageView",start:(new Date()).getTime()});
(function(d,w,id){if(d.getElementById(id))return;var ts=d.createElement("script");ts.type="text/javascript";
ts.async=true;ts.id=id;ts.src="https://top-fwz1.mail.ru/js/code.js";
var f=function(){var s=d.getElementsByTagName("script")[0];s.parentNode.insertBefore(ts,s)};
if(w.opera=="[object Opera]"){d.addEventListener("DOMContentLoaded",f,false)}else{f()}
})(document,window,"tmr-code");
</script>
<!-- /Top.Mail.Ru / MyTarget -->
HTML;
    }

    public function renderNoscript(): string
    {
        $counterId = $this->cfg('counter_id');
        if (empty($counterId)) {
            return '';
        }
        $safeId = $this->escAttr($this->sanitizeId((string) $counterId));
        return '<noscript><div><img src="https://top-fwz1.mail.ru/counter?id=' . $safeId . ';js=na" style="position:absolute;left:-9999px" alt="Top.Mail.Ru" /></div></noscript>';
    }
}
