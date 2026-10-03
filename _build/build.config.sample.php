<?php

declare(strict_types=1);

/**
 * Пример локального конфига сборщика.
 *
 * Скопируйте в _build/build.config.php (он в .gitignore) и подправьте, если
 * базовое поведение не подходит. По умолчанию build.transport.php находит
 * config.core.php соседнего MODX через ../../config.core.php.
 */

// Версия пакета.
//   - MAJOR.MINOR.PATCH
//   - SCRIPTHUB_RELEASE: pl | rc1 | beta2 | dev
define('SCRIPTHUB_VERSION', '1.0.0');
define('SCRIPTHUB_RELEASE', 'pl');

// Принудительный путь к MODX_CORE_PATH (если автодетект не находит config.core.php).
// Оставьте null для автодетекта.
const SCRIPTHUB_MODX_CORE_PATH = null;
