<?php

declare(strict_types=1);

use MODX\Revolution\modExtraManagerController;

/**
 * scriptHub — Base Manager Controller
 */
abstract class ScriptHubManagerController extends modExtraManagerController
{
    public function initialize(): void
    {
        $this->modx->services->get('scripthub');
    }

    public function getLanguageTopics(): array
    {
        return ['scripthub:default'];
    }

    public function checkPermissions(): bool
    {
        return $this->modx->hasPermission('settings');
    }
}

/**
 * Index controller — defines default sub-controller.
 */
class IndexManagerController extends ScriptHubManagerController
{
    public static function getDefaultController(): string
    {
        return 'home';
    }
}
