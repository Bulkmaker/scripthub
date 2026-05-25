# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Что это

**scriptHub** — MODX Revolution 3 extra: единый хаб для управления внешними скриптами (16 сервисов: аналитика, рекламные пиксели, чаты, лидогенерация). Vue 3 admin UI через VueTools, PSR-4 namespace `RenderRoom\ScriptHub`, PHP 8.1+.

Назначение для будущих сессий: понимать **исходники + Vue + xPDO модель**. Это не отдельно стоящее приложение — он живёт внутри MODX-инсталляции, поэтому большинство задач требует прогона в Docker-окружении.

## Команды разработки

```bash
# Vue dev (watch — пересборка при изменениях)
cd assets/components/scripthub/js && npm install && npm run dev

# Vue production build (артефакт: assets/components/scripthub/mgr/vue-dist/scripthub-admin.min.js)
cd assets/components/scripthub/js && npm run build

# Всё PHP-исполнение идёт через Docker контейнер site-app из соседнего репо ~/Documents/GitHub/site.render-room.ru
# (контейнер монтирует modx/ как /var/www/html; запускать всегда от www-data).

# Регенерация xPDO модели из MySQL schema (после правки model/schema/scripthub.mysql.schema.xml)
cd ~/Documents/GitHub/site.render-room.ru
docker compose exec -u www-data app \
  php /var/www/html/core/components/scripthub/build/build.schema.php

# Регистрация компонента в MODX (namespace + menu + plugin + settings + table) —
# auto-registration, НЕ билдер транспортника
docker compose exec -u www-data app \
  php /var/www/html/core/components/scripthub/build/install.php
```

Тестов нет, lint не настроен. Smoke-проверка идёт вручную через MODX manager на dev-сайте.

## Архитектура — big picture

Запрос проходит через две независимые поверхности:

**1. Frontend инъекция (публичный сайт):**
```
HTTP request
  → MODX core (OnMODXInit) → bootstrap.php → autoload + addPackage + DI register
  → MODX render (OnWebPagePrerender) → plugin.scripthub.php
  → ScriptRenderer собирает HTML по getEnabled() сервисам
  → str_replace/preg_replace в $modx->resource->_output подставляет блоки в </head>, <body>, </body>
```

**2. Admin API (менеджер):**
```
Vue компонент (assets/components/scripthub/js/src/admin/)
  → fetch на assets/components/scripthub/connector.php?action=mgr/service/<name>
  → MODX connector маршрутизирует в src/Processors/Service/<Name>.php
  → процессор работает с моделью \scripthub\ScriptHubService (одна таблица, одна строка на service_key)
```

**Ключевые архитектурные сущности:**

- **`AbstractService` + `ServiceRegistry`** (`src/Services/`). Каждый сервис = подкласс `AbstractService` в `Services/<Category>/<Name>/<Name>.php`. Категории: `Analytics`, `Pixels`, `Chats`, `LeadGen`. `ServiceRegistry::discover()` сканирует папки, инстанцирует классы, потом гидрирует из БД enabled/added/config/position. **Auto-discovery, никакой ручной регистрации не нужно.**

- **Self-описание сервиса.** `getKey()`, `getName()`, `getCategory()`, `getFields()` (схема полей формы — `FieldType` enum), `getPosition()` (`InjectionPosition` enum: HEAD / BODY_END / AFTER_BODY_OPEN), `render(): string`, опц. `renderNoscript(): string`. Иконка — `icon.svg` рядом с PHP-файлом или `getIcon()` с PrimeIcons-классом.

- **Хранение состояния.** Одна таблица `modx_scripthub_services`. Поля: `service_key` (FK на класс), `added` (видимость в дашборде), `enabled` (инъекция на фронте), `config` (JSON с значениями полей), `position` (drag-and-drop порядок). Сервисы НЕ хранят свою бизнес-логику в БД — только пользовательскую конфигурацию.

- **Процессоры** (`src/Processors/Service/`): Add, Update, Get, GetList, Toggle, Remove, Sort, RefreshAsset. Каждый проверяет `checkPolicy('settings')`. RefreshAsset скачивает self-hosted tag.js (для Яндекс.Метрики).

- **DI/bootstrap.** `bootstrap.php` регистрирует PSR-4 autoloader (если нет vendor/autoload), вызывает `$modx->addPackage('scripthub', model/)`, регистрирует сервис `scripthub` в `$modx->services`. Защита от двойного include — `SCRIPTHUB_BOOTSTRAPPED`.

## Критические gotchas

1. **Не ставить `declare(strict_types=1)` в `plugin.scripthub.php`.** MODX выполняет content плагинов через `eval()`, strict_types в eval'нутом коде ломает выполнение (commit 06a66a3). В остальных файлах strict_types обязателен.

2. **`build/install.php` — это не билдер транспортника.** Он регистрирует компонент в уже существующей MODX через `$modx->newObject(modNamespace)` etc. Для modstore.pro нужен отдельный `_build/build.transport.php` через `xPDOTransport` — пока не написан. Подробности релизного workflow — в `docs/RELEASE.md` (в `.gitignore`, репо публичный).

3. **Invariant `added` + `enabled` для фронт-инъекции.** Сервис инжектится только если у него ОБА флага в `true`. `ServiceRegistry::getEnabled()` фильтрует по `isEnabled() && isAdded()`. Процессоры `Update`/`Toggle`/`Sort` НЕ создают новые строки — требуют существующую запись с `added=true` (создаётся только через `Add`). Это защищает от direct-POST байпасса add-флоу. Для апгрейда со старых версий нужен upgrade-resolver в `_build/`, который выставит `added=1` для строк где `enabled=1` (TODO).

4. **Vue правится только здесь.** Этот репо = source of truth для Vue. Минифицированный bundle (`mgr/vue-dist/scripthub-admin.min.js`) лежит и здесь, и в `~/Documents/GitHub/site.render-room.ru/modx/`. **Никогда не править минифицированный bundle напрямую** — пересобирать через `npm run build`.

5. **PHP/CSS можно дебажить прямо в site-репо**, потом переносить рабочее сюда (см. workflow в README). После любых правок в этом репо — обязательно `rsync` в `site.render-room.ru/modx/` (или поднимать симлинки).

6. **Lexicon — две локали, en + ru.** Они должны быть в синхроне. Server-side ошибки валидации сейчас иногда захардкожены русским литералом — это баг (см. `docs/RELEASE.md`).

## Добавление нового сервиса

Один файл `core/components/scripthub/src/Services/<Category>/<Name>/<Name>.php` (например `src/Services/Pixels/MyPixel/MyPixel.php`):

```php
<?php
declare(strict_types=1);
namespace RenderRoom\ScriptHub\Services\Pixels\MyPixel;

use RenderRoom\ScriptHub\Services\{AbstractService, ServiceCategory, FieldType, InjectionPosition};

class MyPixel extends AbstractService
{
    public function getKey(): string         { return 'my-pixel'; }
    public function getName(): string        { return 'My Pixel'; }
    public function getCategory(): ServiceCategory { return ServiceCategory::PIXELS; }
    public function getPosition(): InjectionPosition { return InjectionPosition::HEAD; }
    public function getFields(): array       { return [['key'=>'pixel_id', 'type'=>FieldType::TEXT->value, 'required'=>true]]; }
    public function render(): string {
        $id = $this->jsEncode($this->sanitizeId($this->cfg('pixel_id')));
        return "<script>/* … {$id} … */</script>";
    }
}
```

Положить `icon.svg` рядом. После добавления — пересобрать Vue (если он зависит от списка сервисов), `rsync` в site, проверить в дашборде.

**Безопасность сервиса:** весь user input прогонять через хелперы `AbstractService`: `sanitizeId()`, `sanitizeUrl()` (+ allowlist hosts если возможно), `sanitizeHost()`, `jsEncode()` для JS-литералов, `htmlAttr()` для атрибутов. См. commit c947ae5 как референс — там есть и `docs/security.md`.

## Workflow с site.render-room.ru

```
~/Documents/GitHub/
├── scripthub/                  # этот репо: исходники + Vue source + _build/
└── site.render-room.ru/        # production-сайт + Docker MODX (контейнер site-app)
    └── modx/                   # MODX root, монтируется в контейнер как /var/www/html
        ├── core/components/scripthub/        # копия (без Vue source)
        └── assets/components/scripthub/      # копия (без js/ source)
```

Цикл: правка здесь → rsync в site → `docker compose exec -u www-data app php …` → проверка в MODX manager → коммит сюда → копия обратно в site и коммит там. Vue-исходники (`js/`, `node_modules/`) в site-репо никогда не попадают.

## Релиз в modstore.pro

См. `docs/RELEASE.md` (в `.gitignore`, не публикуется). Кратко: собрать Vue → синхронизировать в site/modx → запустить `_build/build.transport.php` через `docker compose exec` → артефакт `.transport.zip` в `core/packages/` → залить через vendor.modstore.pro.
