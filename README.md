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

### Деплой в MODX-сайт

После сборки скопируйте файлы в рабочий MODX:

```bash
# Скопировать core
cp -R core/components/scripthub/ /path/to/modx/core/components/scripthub/

# Скопировать assets (включая собранный JS)
cp -R assets/components/scripthub/ /path/to/modx/assets/components/scripthub/
```

Или используйте симлинки для разработки:

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
        $id = $this->cfg('tracking_id');
        return "<script>/* tracking code for {$id} */</script>";
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

## Лицензия

MIT
