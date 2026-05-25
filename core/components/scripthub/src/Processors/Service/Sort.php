<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Sort extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function process(): mixed
    {
        $order = $this->getProperty('order', '');
        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        if (!is_array($order) || empty($order)) {
            return $this->failure('Invalid order data');
        }

        // Cap batch size — sane upper bound for 16 services × headroom.
        // Prevents DoS via thousands of fabricated entries.
        $order = array_slice($order, 0, 100);

        $now = date('Y-m-d H:i:s');

        foreach ($order as $item) {
            $key = $item['key'] ?? '';
            $position = (int) ($item['position'] ?? 0);
            // Bound position to non-negative reasonable range.
            $position = max(0, min(999999, $position));

            if (empty($key) || !preg_match('/^[a-z0-9\-]{1,50}$/', $key)) {
                continue;
            }

            $row = $this->modx->getObject(\scripthub\ScriptHubService::class, ['service_key' => $key]);
            // Only reorder services that were explicitly added.
            if ($row && $row->get('added')) {
                $row->set('position', $position);
                $row->set('updated_at', $now);
                $row->save();
            }
        }

        return $this->success('');
    }
}
