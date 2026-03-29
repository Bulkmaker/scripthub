<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Processors\Service;

use MODX\Revolution\Processors\Processor;

class RefreshAsset extends Processor
{
    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }

    protected array $supportedAssets = [
        'yandex-metrika' => 'https://mc.yandex.ru/metrika/tag.js',
    ];

    public function process(): mixed
    {
        $serviceKey = $this->getProperty('service_key', '');

        if (empty($serviceKey) || !preg_match('/^[a-z0-9\-]{1,50}$/', $serviceKey)) {
            return $this->failure('Invalid service_key');
        }

        if (!isset($this->supportedAssets[$serviceKey])) {
            return $this->failure('Self-hosting is not supported for this service');
        }

        $sourceUrl = $this->supportedAssets[$serviceKey];

        // Enforce HTTPS
        if (!str_starts_with($sourceUrl, 'https://')) {
            return $this->failure('Only HTTPS sources are allowed');
        }

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
            return $this->failure('Failed to download asset file');
        }

        // Validate size
        if (strlen($content) > $maxSize) {
            return $this->failure('Downloaded file exceeds 2 MB limit');
        }

        // Validate content is JavaScript (not PHP, HTML, etc.)
        if (str_contains($content, '<?php') || str_contains($content, '<?=') || str_contains($content, '<?xml')) {
            return $this->failure('Downloaded content contains PHP/XML tags');
        }

        $trimmed = ltrim($content);
        if (str_starts_with($trimmed, '<!DOCTYPE') || str_starts_with($trimmed, '<html')) {
            return $this->failure('Downloaded content is HTML, not JavaScript');
        }

        // Validate HTTP response headers (Content-Type)
        if (isset($http_response_header)) {
            $contentType = '';
            foreach ($http_response_header as $header) {
                if (stripos($header, 'Content-Type:') === 0) {
                    $contentType = strtolower(trim(substr($header, 13)));
                }
            }
            if ($contentType && !str_contains($contentType, 'javascript') && !str_contains($contentType, 'ecmascript')) {
                return $this->failure('Unexpected Content-Type: expected JavaScript');
            }
        }

        $fileName = basename(parse_url($sourceUrl, PHP_URL_PATH) ?: 'asset.js');
        if (file_put_contents($targetFile, $content) === false) {
            return $this->failure('Failed to write asset to disk');
        }

        $this->modx->log(\MODX\Revolution\modX::LOG_LEVEL_INFO,
            '[scriptHub] Downloaded ' . $fileName . ' (' . strlen($content) . ' bytes)'
        );

        return $this->success('', [
            'file' => $fileName,
            'size' => strlen($content),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
