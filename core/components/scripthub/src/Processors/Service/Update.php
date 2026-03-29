<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Update extends Processor
{
    public function checkPermissions(): bool
    {
        return true;
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');
        $configRaw = $this->getProperty('config', '');

        if (empty($serviceKey)) {
            return $this->failure('service_key is required');
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $service = $scriptHub->getRegistry()->get($serviceKey);

        if (!$service) {
            return $this->failure('Service not found: ' . $serviceKey);
        }

        // Parse config
        $config = is_string($configRaw) ? (json_decode($configRaw, true) ?? []) : (array) $configRaw;

        // Validate
        $errors = $service->validate($config);
        if (!empty($errors)) {
            return $this->failure('Validation failed', ['errors' => $errors]);
        }

        // Save to DB (upsert)
        $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $serviceKey]);
        if (!$row) {
            $row = $this->modx->newObject(\scripthub\ScriptHubService::class);
            $row->set('service_key', $serviceKey);
            $row->set('created_at', date('Y-m-d H:i:s'));
        }

        $row->set('config', json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $row->set('updated_at', date('Y-m-d H:i:s'));

        if (!$row->save()) {
            return $this->failure('Failed to save service config');
        }

        // Update service in memory with full DB row
        $service->hydrate($row->toArray());

        return $this->success('', $service->toArray());
    }
}
