<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Update extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');
        $configRaw = $this->getProperty('config', '');

        if (empty($serviceKey) || !preg_match('/^[a-z0-9\-]{1,50}$/', $serviceKey)) {
            return $this->failure('Invalid service_key');
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $service = $scriptHub->getRegistry()->get($serviceKey);

        if (!$service) {
            return $this->failure('Service not found');
        }

        // Parse config
        $config = is_string($configRaw) ? (json_decode($configRaw, true) ?? []) : (array) $configRaw;

        // Filter config to only allowed keys from field definitions
        $allowedKeys = array_column($service->getFields(), 'key');
        $config = array_intersect_key($config, array_flip($allowedKeys));

        // Validate (loads lexicon for localized error messages)
        $this->modx->lexicon->load('scripthub:default');
        $errors = $service->validate($config);
        if (!empty($errors)) {
            return $this->failure('Validation failed', ['errors' => $errors]);
        }

        // Service must be explicitly added first — Update never creates rows.
        // Prevents direct-POST bypass of the add-flow that would otherwise
        // inject scripts on the frontend for never-added services.
        $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $serviceKey]);
        if (!$row || !$row->get('added')) {
            return $this->failure('Service must be added before it can be configured');
        }

        $row->set('config', json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $row->set('updated_at', date('Y-m-d H:i:s'));

        // Auto-enable if all required fields are filled
        $service->setConfig($config);
        if (!$row->get('enabled') && $service->isConfigured()) {
            $row->set('enabled', true);
        }

        if (!$row->save()) {
            return $this->failure('Failed to save service config');
        }

        // Update service in memory with full DB row
        $service->hydrate($row->toArray());

        return $this->success('', $service->toArray());
    }
}
