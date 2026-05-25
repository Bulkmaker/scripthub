<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\LeadGen\CoMagic;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class CoMagic extends AbstractService
{
    public function getKey(): string
    {
        return 'comagic';
    }

    public function getName(): string
    {
        return 'CoMagic';
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
        return 'CoMagic -- коллтрекинг, онлайн-чат, лидогенерация и сквозная аналитика';
    }

    public function getDocsUrl(): string
    {
        return 'https://www.comagic.ru/support/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'site_id',
                'label' => 'ID сайта',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => '12345',
                'helpText' => 'Числовой идентификатор сайта из личного кабинета CoMagic',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $siteId = $this->cfg('site_id');
        if (empty($siteId)) {
            return '';
        }

        $cleanId = $this->sanitizeId((string) $siteId);
        if ($cleanId === '') {
            return '';
        }
        $safeId = $this->jsEncode($cleanId);

        return <<<HTML
<!-- CoMagic (scriptHub) -->
<script>
var __cs=__cs||[];
__cs.push(["setCs498Id",{$safeId}]);
(function(){var ml=document.createElement("script");ml.type="text/javascript";ml.async=true;
ml.src="https://cdn.comagic.ru/comagic-mod.js";
var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(ml,s);})();
</script>
<!-- /CoMagic -->
HTML;
    }
}
