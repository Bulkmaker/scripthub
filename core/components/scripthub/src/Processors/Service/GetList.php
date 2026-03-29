<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;
use RenderRoom\ScriptHub\Services\ServiceCategory;

class GetList extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    public function process(): mixed
    {
        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $registry = $scriptHub->getRegistry();

        $results = [];
        foreach ($registry->getAll() as $service) {
            $results[] = $service->toArray();
        }

        // Sort by category position (from enum), then by name
        usort($results, function ($a, $b) {
            $catA = ServiceCategory::tryFrom($a['category'])?->position() ?? 99;
            $catB = ServiceCategory::tryFrom($b['category'])?->position() ?? 99;
            if ($catA !== $catB) {
                return $catA <=> $catB;
            }
            return $a['name'] <=> $b['name'];
        });

        return $this->outputArray($results, count($results));
    }
}
