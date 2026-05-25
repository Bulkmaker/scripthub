<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Toggle extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');
        $enabled = (bool) $this->getProperty('enabled', 0);

        if (empty($serviceKey) || !preg_match('/^[a-z0-9\-]{1,50}$/', $serviceKey)) {
            return $this->failure('Invalid service_key');
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $service = $scriptHub->getRegistry()->get($serviceKey);

        if (!$service) {
            return $this->failure('Service not found');
        }

        // Service must be explicitly added first — Toggle never creates rows.
        // Prevents direct-POST bypass that would otherwise create a zombie
        // enabled=1, added=0 row injecting on the frontend but invisible in UI.
        $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $serviceKey]);
        if (!$row || !$row->get('added')) {
            return $this->failure('Service must be added before it can be toggled');
        }

        $row->set('enabled', $enabled);
        $row->set('updated_at', date('Y-m-d H:i:s'));

        if (!$row->save()) {
            return $this->failure('Failed to toggle service');
        }

        return $this->success('');
    }
}
