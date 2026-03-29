<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class Get extends Processor
{
    public function checkPermissions(): bool
    {
        return true;
    }

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');
        if (empty($serviceKey)) {
            return $this->failure('service_key is required');
        }

        /** @var \RenderRoom\ScriptHub\ScriptHub $scriptHub */
        $scriptHub = $this->modx->services->get('scripthub');
        $service = $scriptHub->getRegistry()->get($serviceKey);

        if (!$service) {
            return $this->failure('Service not found: ' . $serviceKey);
        }

        $data = $service->toArray();

        // Add rendered preview if enabled and configured
        if ($service->isEnabled()) {
            $data['preview'] = $service->render();
            $noscript = $service->renderNoscript();
            if ($noscript) {
                $data['preview'] .= "\n" . $noscript;
            }
        }

        return $this->success('', $data);
    }
}
