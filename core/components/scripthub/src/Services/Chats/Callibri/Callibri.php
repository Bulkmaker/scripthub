<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Chats\Callibri;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class Callibri extends AbstractService
{
    public function getKey(): string
    {
        return 'callibri';
    }

    public function getName(): string
    {
        return 'Callibri';
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
        return 'Callibri — МультиЧат, обратный звонок, коллтрекинг и аналитика рекламы';
    }

    public function getDocsUrl(): string
    {
        return 'https://callibri.ru/help';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'site_id',
                'label' => 'ID сайта',
                'type' => FieldType::Text->value,
                'required' => true,
                'default' => '',
                'placeholder' => 'abc123def456',
                'helpText' => 'Идентификатор сайта из личного кабинета Callibri (Настройки → Код для сайта)',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $rawSiteId = (string) $this->cfg('site_id');
        if (empty($rawSiteId)) {
            return '';
        }

        $cleanSiteId = $this->sanitizeId($rawSiteId);
        if ($cleanSiteId === '') {
            return '';
        }
        $safeSiteId = $this->jsEncode($cleanSiteId);

        return <<<HTML
<!-- Callibri (scriptHub) -->
<script src="https://cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
window.callibri_data = window.callibri_data || [];
callibri_data.push({site_id: {$safeSiteId}});
</script>
<!-- /Callibri -->
HTML;
    }
}
