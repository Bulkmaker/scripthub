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
        return 'CoMagic — коллтрекинг, онлайн-чат, лидогенерация и сквозная аналитика. Идентификатор аккаунта (account key) — длинный alphanumeric-хэш из ЛК CoMagic.';
    }

    public function getDocsUrl(): string
    {
        return 'https://help.comagic.ru/knowledge-bases/12/articles/1590-dobavlenie-sajta-i-ustanovka-koda-comagic';
    }

    public function getFields(): array
    {
        return [
            [
                'key' => 'account_key',
                'label' => 'Account key (ID аккаунта)',
                'type' => FieldType::Text->value,
                'required' => true,
                'placeholder' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
                'helpText' => 'Уникальный ключ аккаунта из личного кабинета CoMagic (Настройки → Установка кода). Длинный alphanumeric-хэш, не числовой ID сайта.',
            ],
            [
                'key' => 'cookie_domain',
                'label' => 'Cookie domain (опционально)',
                'type' => FieldType::Text->value,
                'default' => '',
                'placeholder' => '.example.com',
                'helpText' => 'Для мульти-субдоменной инсталляции — общий домен с точкой впереди. Можно оставить пустым.',
            ],
        ];
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::BodyEnd;
    }

    public function render(): string
    {
        $accountKey = $this->cfg('account_key');
        if (empty($accountKey)) {
            return '';
        }

        $cleanKey = $this->sanitizeId((string) $accountKey);
        if ($cleanKey === '') {
            return '';
        }
        $safeKey = $this->jsEncode($cleanKey);

        $cookieDomainCall = '';
        $cookieDomain = trim((string) $this->cfg('cookie_domain', ''));
        if ($cookieDomain !== '' && preg_match('/^\.?[a-z0-9.\-]+$/i', $cookieDomain)) {
            $safeDomain = $this->jsEncode($cookieDomain);
            $cookieDomainCall = "\n__cs.push([\"setCookieDomain\",{$safeDomain}]);";
        }

        return <<<HTML
<!-- CoMagic (scriptHub) -->
<script>
var __cs=__cs||[];
__cs.push(["setCsAccount",{$safeKey}]);{$cookieDomainCall}
(function(){var ml=document.createElement("script");ml.type="text/javascript";ml.async=true;
ml.src="https://app.comagic.ru/static/cs.min.js";
var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(ml,s);})();
</script>
<!-- /CoMagic -->
HTML;
    }
}
