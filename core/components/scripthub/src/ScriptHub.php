<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub;

use MODX\Revolution\modX;
use RenderRoom\ScriptHub\Services\ServiceRegistry;

class ScriptHub
{
    public const VERSION = '1.0.0-beta';

    public readonly modX $modx;

    /** @var array<string, mixed> */
    protected array $config;

    protected ?ServiceRegistry $registry = null;

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;

        $corePath = $modx->getOption(
            'scripthub.core_path',
            $config,
            $modx->getOption('core_path') . 'components/scripthub/'
        );
        $assetsPath = $modx->getOption(
            'scripthub.assets_path',
            $config,
            $modx->getOption('assets_path') . 'components/scripthub/'
        );
        $assetsUrl = $modx->getOption(
            'scripthub.assets_url',
            $config,
            $modx->getOption('assets_url') . 'components/scripthub/'
        );

        $this->config = array_merge([
            'corePath'       => $corePath,
            'srcPath'        => $corePath . 'src/',
            'modelPath'      => $corePath . 'model/',
            'processorsPath' => $corePath . 'src/Processors/',
            'assetsPath'     => $assetsPath,
            'assetsUrl'      => $assetsUrl,
            'cssUrl'         => $assetsUrl . 'css/',
            'jsUrl'          => $assetsUrl . 'js/',
            'connectorUrl'   => $assetsUrl . 'connector.php',
        ], $config);
    }

    public function getRegistry(): ServiceRegistry
    {
        if ($this->registry === null) {
            $this->registry = new ServiceRegistry($this->modx);
            $this->registry->discover();
        }
        return $this->registry;
    }

    /**
     * @return mixed
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? $default;
    }

    public function getVersion(): string
    {
        return self::VERSION;
    }
}
