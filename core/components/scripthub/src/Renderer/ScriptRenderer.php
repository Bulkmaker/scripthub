<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Renderer;

use RenderRoom\ScriptHub\ScriptHub;
use RenderRoom\ScriptHub\Services\InjectionPosition;

class ScriptRenderer
{
    protected ScriptHub $scriptHub;

    public function __construct(ScriptHub $scriptHub)
    {
        $this->scriptHub = $scriptHub;
    }

    /**
     * Render all enabled services for a given injection position.
     */
    public function renderForPosition(InjectionPosition $position): string
    {
        $registry = $this->scriptHub->getRegistry();
        $enabled = $registry->getEnabled();

        $output = '';
        foreach ($enabled as $service) {
            if ($service->getInjectionPosition() === $position) {
                $rendered = $service->render();
                if ($rendered !== '') {
                    $output .= $rendered . "\n";
                }
            }
        }

        return $output;
    }

    /**
     * Render all noscript fallbacks for enabled services.
     */
    public function renderNoscriptAll(): string
    {
        $registry = $this->scriptHub->getRegistry();
        $output = '';

        foreach ($registry->getEnabled() as $service) {
            $ns = $service->renderNoscript();
            if ($ns !== '') {
                $output .= $ns . "\n";
            }
        }

        return $output;
    }
}
