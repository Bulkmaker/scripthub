<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Analytics;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class Roistat extends AbstractService
{
    public function getKey(): string
    {
        return 'roistat';
    }

    public function getName(): string
    {
        return 'Roistat';
    }

    public function getCategory(): ServiceCategory
    {
        return ServiceCategory::Analytics;
    }

    public function getIcon(): string
    {
        return 'pi pi-chart-line';
    }

    public function getDescription(): string
    {
        return 'Roistat -- сквозная аналитика, коллтрекинг и управление рекламными кампаниями';
    }

    public function getDocsUrl(): string
    {
        return 'https://help.roistat.com/';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'project_id',
                'label' => 'ID проекта',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'abcdef1234567890',
                'helpText' => 'Идентификатор проекта из настроек Roistat (Настройки > Счётчик)',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    public function render(): string
    {
        $projectId = $this->cfg('project_id');
        if (empty($projectId)) {
            return '';
        }

        $safeId = $this->jsEncode($this->sanitizeId((string) $projectId));

        return <<<HTML
<!-- Roistat (scriptHub) -->
<script>
(function(w,d,s,h,id){w.roistatProjectId=id;w.roistatHost=h;
var p=d.location.protocol=="https:"?"https://":"http://";
var u=(/^.*roistat_visit=[^;]+(;\s*|$)/.test(d.cookie)?"/dist/module.js":"/api/site/1.0/"+id+"/init?referrer="+encodeURIComponent(d.location.href));
var js=d.createElement(s);js.charset="UTF-8";js.async=1;js.src=p+h+u;
var js2=d.getElementsByTagName(s)[0];js2.parentNode.insertBefore(js,js2);
})(window,document,"script","cloud.roistat.com",{$safeId});
</script>
<!-- /Roistat -->
HTML;
    }
}
