<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services;

use MODX\Revolution\modX;

class ServiceRegistry
{
    protected modX $modx;

    /** @var array<string, AbstractService> */
    protected array $services = [];

    protected bool $discovered = false;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
    }

    /**
     * Auto-discover all service classes from subdirectories.
     */
    public function discover(): void
    {
        if ($this->discovered) {
            return;
        }

        $basePath = __DIR__ . '/';
        $dirs = [
            'Analytics' => 'RenderRoom\\ScriptHub\\Services\\Analytics\\',
            'Pixels'    => 'RenderRoom\\ScriptHub\\Services\\Pixels\\',
            'Chats'     => 'RenderRoom\\ScriptHub\\Services\\Chats\\',
            'LeadGen'   => 'RenderRoom\\ScriptHub\\Services\\LeadGen\\',
        ];

        foreach ($dirs as $dir => $namespace) {
            $path = $basePath . $dir;
            if (!is_dir($path)) {
                continue;
            }

            // Each service lives in its own subfolder: Category/ServiceName/ServiceName.php
            foreach (scandir($path) as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $subDir = $path . '/' . $entry;
                $file = $subDir . '/' . $entry . '.php';
                if (!is_dir($subDir) || !file_exists($file)) {
                    continue;
                }

                $className = $namespace . $entry . '\\' . $entry;
                if (!class_exists($className)) {
                    continue;
                }

                try {
                    $instance = new $className($this->modx);
                    if ($instance instanceof AbstractService) {
                        $this->services[$instance->getKey()] = $instance;
                    }
                } catch (\Throwable $e) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR,
                        "[scriptHub] Failed to load service {$className}: " . $e->getMessage()
                    );
                }
            }
        }

        $this->hydrateFromDatabase();
        $this->discovered = true;
    }

    protected function hydrateFromDatabase(): void
    {
        try {
            $rows = $this->modx->getCollection(\scripthub\ScriptHubService::class);
            if (!$rows) {
                return;
            }
            foreach ($rows as $row) {
                $key = $row->get('service_key');
                if (isset($this->services[$key])) {
                    $this->services[$key]->hydrate($row->toArray());
                }
            }
        } catch (\Throwable $e) {
            // Table may not exist yet
            $this->modx->log(modX::LOG_LEVEL_DEBUG,
                '[scriptHub] Could not load services from DB: ' . $e->getMessage()
            );
        }
    }

    public function get(string $key): ?AbstractService
    {
        return $this->services[$key] ?? null;
    }

    /**
     * @return AbstractService[]
     */
    public function getAll(): array
    {
        return $this->services;
    }

    /**
     * @return AbstractService[]
     */
    public function getByCategory(ServiceCategory $category): array
    {
        return array_filter(
            $this->services,
            fn(AbstractService $s) => $s->getCategory() === $category
        );
    }

    /**
     * Frontend injection set: only services that were explicitly added AND enabled.
     * Both flags must be true — see Add.php / Update.php / Toggle.php.
     */
    public function getEnabled(): array
    {
        $enabled = array_filter(
            $this->services,
            fn(AbstractService $s) => $s->isEnabled() && $s->isAdded()
        );

        uasort($enabled, fn(AbstractService $a, AbstractService $b) =>
            $a->getPosition() <=> $b->getPosition()
        );

        return $enabled;
    }
}
