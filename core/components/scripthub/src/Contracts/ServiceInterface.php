<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Contracts;

use RenderRoom\ScriptHub\Services\InjectionPosition;
use RenderRoom\ScriptHub\Services\ServiceCategory;

interface ServiceInterface
{
    public function getKey(): string;

    public function getName(): string;

    public function getCategory(): ServiceCategory;

    public function getIcon(): string;

    public function getDescription(): string;

    public function getDocsUrl(): string;

    public function getFields(): array;

    public function getInjectionPosition(): InjectionPosition;

    public function isEnabled(): bool;

    public function getConfig(): array;

    public function setConfig(array $config): void;

    /**
     * @return array<string, string> Field key => error message
     */
    public function validate(array $config): array;

    public function render(): string;

    public function renderNoscript(): string;
}
