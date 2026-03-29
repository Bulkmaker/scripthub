<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services;

use MODX\Revolution\modX;
use RenderRoom\ScriptHub\Contracts\ServiceInterface;

abstract class AbstractService implements ServiceInterface
{
    protected modX $modx;
    protected bool $enabled = false;
    protected array $config = [];
    protected int $position = 0;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
    }

    public function getDocsUrl(): string
    {
        return '';
    }

    public function renderNoscript(): string
    {
        return '';
    }

    public function getInjectionPosition(): InjectionPosition
    {
        return InjectionPosition::Head;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Hydrate service state from a DB row.
     */
    public function hydrate(array $row): void
    {
        $this->enabled = (bool) ($row['enabled'] ?? false);
        $this->config = is_string($row['config'] ?? null)
            ? (json_decode($row['config'], true) ?? [])
            : ($row['config'] ?? []);
        $this->position = (int) ($row['position'] ?? 0);
    }

    public function validate(array $config): array
    {
        $errors = [];
        foreach ($this->getFields() as $field) {
            if (($field['required'] ?? false) && empty($config[$field['key']] ?? '')) {
                $errors[$field['key']] = 'Это поле обязательно';
            }
        }
        return $errors;
    }

    /**
     * Get a config value with default.
     */
    protected function cfg(string $key, mixed $default = ''): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Escape value for safe interpolation into JavaScript string literal.
     * Use json_encode to produce a valid JS string (with quotes).
     */
    protected function jsEncode(mixed $value): string
    {
        return json_encode((string) $value, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    /**
     * Escape for HTML attribute context.
     */
    protected function escAttr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate that a value matches expected format (alphanumeric + dashes).
     * Returns sanitized value or empty string.
     */
    protected function sanitizeId(string $value): string
    {
        return preg_match('/^[a-zA-Z0-9_\-]+$/', $value) ? $value : '';
    }

    /**
     * Validate URL: must be https with allowed host.
     */
    protected function sanitizeUrl(string $url, array $allowedHosts = []): string
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            return '';
        }
        if (!in_array($parsed['scheme'], ['https', 'http'], true)) {
            return '';
        }
        if (!empty($allowedHosts) && !in_array($parsed['host'], $allowedHosts, true)) {
            return '';
        }
        return $url;
    }

    /**
     * Check if a required field has a value.
     */
    protected function isConfigured(): bool
    {
        foreach ($this->getFields() as $field) {
            if (($field['required'] ?? false) && empty($this->config[$field['key']] ?? '')) {
                return false;
            }
        }
        return true;
    }

    /**
     * Serialize to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'category' => $this->getCategory()->value,
            'categoryLabel' => $this->getCategory()->label(),
            'categoryIcon' => $this->getCategory()->icon(),
            'icon' => $this->getIcon(),
            'description' => $this->getDescription(),
            'docsUrl' => $this->getDocsUrl(),
            'injectionPosition' => $this->getInjectionPosition()->value,
            'enabled' => $this->enabled,
            'configured' => $this->isConfigured(),
            'config' => $this->config,
            'position' => $this->position,
            'fields' => $this->getFields(),
        ];
    }
}
