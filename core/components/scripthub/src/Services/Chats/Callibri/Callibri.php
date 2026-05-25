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
        return 'Callibri — МультиЧат, обратный звонок, коллтрекинг и аналитика рекламы. Привязка к проекту настраивается в личном кабинете Callibri по домену сайта — дополнительных параметров вводить не нужно.';
    }

    public function getDocsUrl(): string
    {
        return 'https://callibri.ru/help/ustanovka_skripta_callibri/kak_ustanovit_skript_callibri_napryamuyu_v_kod_sayta';
    }

    public function getFields(): array
    {
        // Callibri идентифицирует сайт по домену из личного кабинета,
        // runtime-параметров (site_id и т.п.) официальный snippet не принимает.
        return [];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        return <<<HTML
<!-- Callibri (scriptHub) -->
<script src="//cdn.callibri.ru/callibri.js" type="text/javascript" charset="utf-8" defer></script>
<!-- /Callibri -->
HTML;
    }
}
