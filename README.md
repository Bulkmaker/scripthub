# scriptHub

Единый хаб управления внешними скриптами для **MODX Revolution 3**.

Аналитика, рекламные пиксели, чаты, лидогенерация — всё в одном месте с красивым Vue-интерфейсом.

## Возможности

- **16 сервисов** из коробки: Яндекс Метрика, Google Analytics GA4, VK Pixel, JivoSite и другие
- **Vue 3 + PrimeVue** админ-панель через VueTools
- **Модульная архитектура** — добавление нового сервиса = один PHP-файл
- **Self-hosted** режим для Яндекс Метрики (tag.js на вашем сервере)
- **Автоматическая инъекция** скриптов в `<head>`, `<body>` или после `<body>`

## Поддерживаемые сервисы

| Категория | Сервисы |
|-----------|---------|
| Аналитика | Яндекс Метрика, Google Analytics GA4, Matomo, Roistat |
| Рекламные пиксели | VK Pixel, MyTarget, Facebook Pixel, TikTok Pixel |
| Чаты | JivoSite, Carrot Quest, Callibri, Яндекс Мессенджер |
| Лидогенерация | Marquiz, SendPulse, Calltouch, CoMagic |

## Требования

- MODX Revolution 3.x
- PHP 8.1+
- [VueTools](https://modstore.pro/packages/utilities/vuetools) (для админ-панели)

## Структура проекта

```
scripthub/
├── core/components/scripthub/
│   ├── bootstrap.php              # PSR-4 autoloader + DI
│   ├── composer.json              # Autoload config
│   ├── index.class.php            # CMP base controller
│   ├── build/
│   │   ├── build.schema.php       # xPDO schema → model
│   │   └── install.php            # Package builder
│   ├── controllers/
│   │   └── home.class.php         # CMP home controller
│   ├── elements/plugins/
│   │   └── plugin.scripthub.php   # Frontend script injection
│   ├── lexicon/
│   │   ├── en/default.inc.php
│   │   └── ru/default.inc.php
│   ├── model/                     # xPDO model (generated)
│   └── src/
│       ├── ScriptHub.php          # Main service class
│       ├── Contracts/
│       │   └── ServiceInterface.php
│       ├── Services/
│       │   ├── AbstractService.php
│       │   ├── ServiceRegistry.php
│       │   ├── ServiceCategory.php  # Enum
│       │   ├── FieldType.php        # Enum
│       │   ├── InjectionPosition.php # Enum
│       │   ├── Analytics/           # 4 сервиса
│       │   ├── Pixels/              # 4 сервиса
│       │   ├── Chats/               # 4 сервиса
│       │   └── LeadGen/             # 4 сервиса
│       ├── Processors/Service/
│       │   ├── GetList.php
│       │   ├── Get.php
│       │   ├── Update.php
│       │   ├── Toggle.php
│       │   └── RefreshAsset.php
│       └── Renderer/
│           └── ScriptRenderer.php
├── assets/components/scripthub/
│   ├── connector.php
│   ├── css/scripthub.css
│   └── js/                        # Vue source code
│       ├── package.json
│       ├── vite.config.js
│       └── src/
│           ├── admin.js           # Vue entry point
│           ├── admin/             # Vue components
│           ├── stores/            # Pinia store
│           └── composables/       # API composable
```

## Разработка

### Первоначальная настройка

```bash
# Клонировать репозиторий
git clone https://github.com/Bulkmaker/scripthub.git

# Установить зависимости для Vue
cd scripthub/assets/components/scripthub/js
npm install
```

### Сборка Vue-приложения

```bash
cd assets/components/scripthub/js

# Development build
npm run build

# Watch mode (пересборка при изменениях)
npm run dev
```

Результат сборки: `assets/components/scripthub/mgr/vue-dist/scripthub-admin.min.js`

### Workflow с сайтом site.render-room.ru

Сайт-репо (`site.render-room.ru`) содержит только **собранный** компонент (без Vue-исходников).

**Порядок работы:**

1. **Дебаг/тестирование** — PHP и CSS можно редактировать прямо в сайт-репо для быстрой проверки. Vue — только через сборку здесь.
2. **Проверка** — убедиться что изменения работают в менеджере MODX.
3. **Перенос сюда** — скопировать рабочие изменения из сайта в этот репо, закоммитить.
4. **Обновить сайт** — скопировать собранный компонент обратно в сайт-репо, закоммитить.

**Копирование в сайт (после сборки):**

```bash
SRC=~/Documents/GitHub/scripthub
DEST=~/Documents/GitHub/site.render-room.ru/modx

# Core (PHP)
cp -R "$SRC/core/components/scripthub/" "$DEST/core/components/scripthub/"

# Assets (connector, CSS, built JS)
cp "$SRC/assets/components/scripthub/connector.php" "$DEST/assets/components/scripthub/"
cp -R "$SRC/assets/components/scripthub/css/" "$DEST/assets/components/scripthub/css/"
cp -R "$SRC/assets/components/scripthub/mgr/" "$DEST/assets/components/scripthub/mgr/"
```

> **Не копировать** `js/`, `node_modules/` в сайт — там только исходники для разработки.

Или используйте симлинки:

```bash
ln -s /path/to/scripthub/core/components/scripthub /path/to/modx/core/components/scripthub
ln -s /path/to/scripthub/assets/components/scripthub /path/to/modx/assets/components/scripthub
```

### Генерация xPDO-модели

```bash
cd /path/to/modx
php core/components/scripthub/build/build.schema.php
```

### Сборка transport-пакета

```bash
cd /path/to/modx
php core/components/scripthub/build/install.php
```

Пакет появится в `core/packages/`.

## Архитектура

### Добавление нового сервиса

Создайте PHP-файл в соответствующей категории (`Analytics/`, `Pixels/`, `Chats/`, `LeadGen/`):

```php
<?php
declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services\Analytics;

use RenderRoom\ScriptHub\Services\AbstractService;
use RenderRoom\ScriptHub\Services\ServiceCategory;
use RenderRoom\ScriptHub\Services\FieldType;
use RenderRoom\ScriptHub\Services\InjectionPosition;

class MyNewService extends AbstractService
{
    public function getKey(): string { return 'my-new-service'; }
    public function getName(): string { return 'My New Service'; }
    public function getDescription(): string { return 'Description here'; }
    public function getCategory(): ServiceCategory { return ServiceCategory::ANALYTICS; }
    public function getIcon(): string { return 'pi pi-chart-bar'; }

    public function getFields(): array
    {
        return [
            [
                'key' => 'tracking_id',
                'label' => 'Tracking ID',
                'type' => FieldType::TEXT->value,
                'required' => true,
                'placeholder' => 'UA-XXXXX-Y',
            ],
        ];
    }

    public function getPosition(): InjectionPosition
    {
        return InjectionPosition::HEAD;
    }

    public function render(): string
    {
        $id = $this->sanitizeId((string) $this->cfg('tracking_id'));
        if ($id === '') return '';

        $safeId = $this->jsEncode($id);
        return "<script>/* tracking code for {$safeId} */</script>";
    }
}
```

Сервис автоматически появится в админ-панели — `ServiceRegistry` сканирует папки при загрузке.

### БД — таблица `scripthub_services`

| Поле | Тип | Назначение |
|------|-----|-----------|
| id | int AI PK | — |
| service_key | varchar(100) UNIQUE | Ключ сервиса |
| enabled | tinyint(1) | Включён/выключен |
| config | mediumtext (JSON) | Настройки сервиса |
| position | int | Порядок вывода |
| created_at | datetime | — |
| updated_at | datetime | — |

### Инъекция скриптов

Плагин на событие `OnWebPagePrerender` собирает HTML всех активных и настроенных сервисов через `ScriptRenderer` и вставляет в соответствующие позиции страницы:

- `head` — перед `</head>`
- `body_end` — перед `</body>`
- `after_body_open` — сразу после `<body>`

## Безопасность

### Правила для разработки сервисов

- **JS-контекст**: значения внутри `<script>` → `$this->jsEncode()` (НЕ `htmlspecialchars`!)
- **HTML-контекст**: значения в атрибутах → `$this->escAttr()`
- **ID/ключи**: `$this->sanitizeId()` — только `[a-zA-Z0-9_-]`
- **URL**: `$this->sanitizeUrl($url, $allowedHosts)` — с allowlist доменов
- **Permissions**: `checkPermissions()` → `$this->modx->hasPermission('settings')`
- **service_key**: regex `/^[a-z0-9\-]{1,50}$/` во всех процессорах
- **Config**: фильтровать ключи по `getFields()` перед сохранением в БД
- **Error messages**: не включать user input в `failure()` ответы
- **Плагины**: НЕ использовать `declare(strict_types=1)` — MODX выполняет их через eval()

### Хелперы в AbstractService

| Метод | Контекст | Пример |
|-------|----------|--------|
| `$this->jsEncode($val)` | JS-строка в `<script>` | `ym({$this->jsEncode($id)}, "init")` |
| `$this->escAttr($val)` | HTML-атрибут | `src="https://example.com/{$this->escAttr($id)}"` |
| `$this->sanitizeId($val)` | Проверка формата | Возвращает `''` если не alphanumeric |
| `$this->sanitizeUrl($url, $hosts)` | Проверка URL | Возвращает `''` если домен не в списке |

## Лицензия

MIT
