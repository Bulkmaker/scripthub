<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services;

use MODX\Revolution\modX;
use RenderRoom\ScriptHub\Contracts\ServiceInterface;

abstract class AbstractService implements ServiceInterface
{
    protected modX $modx;
    protected bool $enabled = false;
    protected bool $added = false;
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

    public function isAdded(): bool
    {
        return $this->added;
    }

    /**
     * Read SVG icon from the service's own directory.
     */
    public function getIconSvg(): string
    {
        $path = dirname((new \ReflectionClass($this))->getFileName()) . '/icon.svg';
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return '';
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
        $this->added = (bool) ($row['added'] ?? false);
        $this->config = is_string($row['config'] ?? null)
            ? (json_decode($row['config'], true) ?? [])
            : ($row['config'] ?? []);
        $this->position = (int) ($row['position'] ?? 0);
    }

    public function validate(array $config): array
    {
        $errors = [];
        $requiredMsg = $this->modx->lexicon('scripthub_field_required');
        if ($requiredMsg === '' || $requiredMsg === 'scripthub_field_required') {
            $requiredMsg = 'This field is required';
        }
        foreach ($this->getFields() as $field) {
            if (($field['required'] ?? false) && empty($config[$field['key']] ?? '')) {
                $errors[$field['key']] = $requiredMsg;
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
    public function isConfigured(): bool
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
            'iconSvg' => $this->getIconSvg(),
            'description' => $this->getDescription(),
            'docsUrl' => $this->getDocsUrl(),
            'injectionPosition' => $this->getInjectionPosition()->value,
            'enabled' => $this->enabled,
            'added' => $this->added,
            'configured' => $this->isConfigured(),
            'config' => $this->config,
            'position' => $this->position,
            'fields' => $this->getFields(),
        ];
    }
}
