<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Add extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');

        if (empty($serviceKey) || !preg_match('/^[a-z0-9\-]{1,50}$/', $serviceKey)) {
            return $this->failure('Invalid service_key');
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $service = $scriptHub->getRegistry()->get($serviceKey);

        if (!$service) {
            return $this->failure('Service not found');
        }

        // Get max position
        $maxPos = 0;
        $rows = $this->modx->getCollection(\scripthub\ScriptHubService::class);
        foreach ($rows as $r) {
            $pos = (int) $r->get('position');
            if ($pos > $maxPos) {
                $maxPos = $pos;
            }
        }

        // Upsert
        $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $serviceKey]);
        if (!$row) {
            $row = $this->modx->newObject(\scripthub\ScriptHubService::class);
            $row->set('service_key', $serviceKey);
            $row->set('config', '{}');
            $row->set('enabled', false);
            $row->set('created_at', date('Y-m-d H:i:s'));
        }

        $row->set('added', true);
        $row->set('position', $maxPos + 1);
        $row->set('updated_at', date('Y-m-d H:i:s'));

        if (!$row->save()) {
            return $this->failure('Failed to add service');
        }

        return $this->success('');
    }
}
