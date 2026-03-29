<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class RefreshAsset extends Processor
{
    public function checkPermissions(): bool
    {
        return true;
    }

    protected array $supportedAssets = [
        'yandex-metrika' => 'https://mc.yandex.ru/metrika/tag.js',
    ];

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');

        if (empty($serviceKey)) {
            return $this->failure('service_key is required');
        }

        if (!isset($this->supportedAssets[$serviceKey])) {
            return $this->failure('Self-hosting is not supported for this service');
        }

        $sourceUrl = $this->supportedAssets[$serviceKey];

        $assetsPath = $this->modx->getOption(
            'scripthub.assets_path',
            null,
            $this->modx->getOption('assets_path') . 'components/scripthub/'
        );

        $targetDir = $assetsPath . 'vendor/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetFile = $targetDir . basename($sourceUrl);

        // Download with safety checks
        $maxSize = 2 * 1024 * 1024; // 2 MB limit

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'MODX-scriptHub/1.0',
                'follow_location' => 0, // No redirects
                'max_redirects' => 0,
            ],
        ]);

        $content = @file_get_contents($sourceUrl, false, $context);
        if ($content === false) {
            return $this->failure('Failed to download ' . basename($sourceUrl) . ' from ' . $sourceUrl);
        }

        // Validate size
        if (strlen($content) > $maxSize) {
            return $this->failure('Downloaded file exceeds 2 MB limit');
        }

        // Validate content is JavaScript (not PHP, HTML, etc.)
        $trimmed = ltrim($content);
        if (str_starts_with($trimmed, '<?php') || str_starts_with($trimmed, '<?=') || str_starts_with($trimmed, '<!DOCTYPE')) {
            return $this->failure('Downloaded content is not a valid JavaScript file');
        }

        if (file_put_contents($targetFile, $content) === false) {
            return $this->failure('Failed to write ' . basename($sourceUrl) . ' to disk');
        }

        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO,
            '[scriptHub] Downloaded ' . basename($sourceUrl) . ' (' . strlen($content) . ' bytes)'
        );

        return $this->success('', [
            'file' => basename($sourceUrl),
            'size' => strlen($content),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
