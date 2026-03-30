<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Remove extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');
        $clearConfig = (bool) $this->getProperty('clear_config', false);

        if (empty($serviceKey) || !preg_match('/^[a-z0-9\-]{1,50}$/', $serviceKey)) {
            return $this->failure('Invalid service_key');
        }

        $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $serviceKey]);
        if (!$row) {
            return $this->failure('Service not found');
        }

        $row->set('added', false);
        $row->set('enabled', false);
        if ($clearConfig) {
            $row->set('config', '{}');
        }
        $row->set('updated_at', date('Y-m-d H:i:s'));

        if (!$row->save()) {
            return $this->failure('Failed to remove service');
        }

        return $this->success('');
    }
}
