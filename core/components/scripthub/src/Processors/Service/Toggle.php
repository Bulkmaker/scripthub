<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Toggle extends Processor
{
    public function checkPermissions(): bool
    {
        return true;
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');
        $enabled = (bool) $this->getProperty('enabled', 0);

        if (empty($serviceKey)) {
            return $this->failure('service_key is required');
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $service = $scriptHub->getRegistry()->get($serviceKey);

        if (!$service) {
            return $this->failure('Service not found: ' . $serviceKey);
        }

        // Upsert
        $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $serviceKey]);
        if (!$row) {
            $row = $this->modx->newObject(\scripthub\ScriptHubService::class);
            $row->set('service_key', $serviceKey);
            $row->set('config', '{}');
            $row->set('created_at', date('Y-m-d H:i:s'));
        }

        $row->set('enabled', $enabled);
        $row->set('updated_at', date('Y-m-d H:i:s'));

        if (!$row->save()) {
            return $this->failure('Failed to toggle service');
        }

        return $this->success('');
    }
}
