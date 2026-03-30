# Add Service Flow — Design Document

## Summary

Переход от «все 16 сервисов видны сразу» к явному добавлению: пустой дашборд → кнопка «Добавить сервис» → диалог выбора → карточка на дашборде → настройка → авто-активация. Drag & drop сортировка, удаление с опцией очистки конфига.

---

## 1. Структура файлов сервисов

Каждый сервис переезжает из плоского файла в свою папку с `icon.svg`:

```
Services/Analytics/
├── YandexMetrika/
│   ├── YandexMetrika.php
│   └── icon.svg
├── GoogleAnalytics/
│   ├── GoogleAnalytics.php
│   └── icon.svg
├── Matomo/
│   ├── Matomo.php
│   └── icon.svg
└── Roistat/
    ├── Roistat.php
    └── icon.svg
```

Аналогично для `Pixels/`, `Chats/`, `LeadGen/`.

### ServiceRegistry

Обновляется — сканирует папки вместо файлов. В каждой папке ищет PHP-файл с классом, наследующим `AbstractService`.

### AbstractService

Новый метод `getIconSvg()`:

```php
public function getIconSvg(): string
{
    $path = dirname((new \ReflectionClass($this))->getFileName()) . '/icon.svg';
    if (file_exists($path)) {
        return file_get_contents($path);
    }
    return '';
}
```

`toArray()` добавляет поле `iconSvg` в ответ API. Старое поле `icon` (PrimeIcons класс) остаётся как fallback.

---

## 2. БД — новое поле `added`

Таблица `scripthub_services` — новое поле:

```sql
added  tinyint(1)  DEFAULT 0  — сервис добавлен на дашборд
```

### Семантика полей

| Поле | Значение |
|------|----------|
| `added=0` | Не на дашборде, доступен в диалоге «Добавить» |
| `added=1` | Карточка на дашборде |
| `enabled=1` | Скрипт активен на фронтенде (инъекция в HTML) |

### Поле `position`

Уже есть в таблице. Используется для drag & drop сортировки. При добавлении: `position = MAX(position) + 1`.

### Жизненный цикл сервиса

1. Пользователь нажал «Добавить» → запись: `added=1, enabled=0, config={}`
2. Открыл drawer, заполнил, сохранил → `enabled=1` автоматически (если required-поля заполнены)
3. Toggle на карточке → `enabled` вкл/выкл
4. «Удалить» → confirm с чекбоксом «Очистить настройки?»
   - Чекбокс выключен: `added=0, enabled=0` (конфиг сохраняется)
   - Чекбокс включен: `added=0, enabled=0, config={}`

---

## 3. Новые процессоры PHP

| Процессор | Действие | Параметры |
|-----------|----------|-----------|
| `Add.php` | Создаёт/обновляет запись: `added=1, enabled=0`, `position=MAX+1` | `service_key` |
| `Remove.php` | `added=0, enabled=0`, опционально `config={}` | `service_key, clear_config` |
| `Sort.php` | Обновляет `position` для массива сервисов | `order: [{key, position}]` |

### Изменения в существующих процессорах

**GetList.php** — добавляет `added`, `iconSvg` в ответ. Сортировка добавленных по `position`.

**Update.php** — после успешного сохранения, если все required-поля заполнены и `enabled=0` → автоматически ставит `enabled=1`.

---

## 4. Vue — диалог «Добавить сервис»

Новый компонент `AddServiceDialog.vue`:

- PrimeVue `Dialog` (модальное окно)
- Заголовок: «Добавить сервис»
- Поиск сверху — InputText для фильтрации по названию
- Компактная сетка карточек, группировка по категориям
- Каждая карточка: `iconSvg` (32x32) + название
- Показываются только сервисы с `added=0`
- Клик → `store.addService(key)` → диалог закрывается → карточка на дашборде → drawer открывается для настройки

### Кнопка в шапке

Рядом с поиском:
```
[+ Добавить сервис]   [Поиск сервисов...]
```

### Empty state

Когда на дашборде пусто:
```
Нет добавленных сервисов
Добавьте первый сервис для управления скриптами
[+ Добавить сервис]
```

### Дашборд

`ServiceDashboard.vue` показывает только `added=1` сервисы. Плоская сетка (без группировки по категориям). Сортировка по `position` (drag & drop).

---

## 5. Drag & drop

Нативный HTML5 drag (без внешних зависимостей — VueTools import map не включает vuedraggable).

- `draggable="true"` на каждой карточке
- `@dragstart`, `@dragover`, `@drop` на контейнере
- При `drop` → пересчёт `position` → `store.sortServices([{key, position}])` → `Sort.php`
- Визуал: карточка полупрозрачная при перетаскивании, линия-индикатор места вставки

---

## 6. Удаление сервиса

Кнопка в drawer, внизу после «Сохранить / Отмена»:

```
[Сохранить]  [Отмена]

─────────────────────
[Удалить сервис]  (severity="danger", text)
```

PrimeVue `ConfirmDialog` при клике:

```
Удалить «Яндекс Метрика»?
Сервис будет отключён и убран с дашборда.

[ ] Очистить настройки

[Отмена]  [Удалить]
```

Чекбокс «Очистить настройки» — по умолчанию выключен.

---

## 7. Pinia store — изменения

### Новые методы

```js
addService(key)                    // → Add.php → обновить список
removeService(key, clearConfig)    // → Remove.php → обновить список
sortServices(order)                // → Sort.php → обновить positions
```

### Новые computed

```js
addedServices      // services.filter(s => s.added) — для дашборда
availableServices  // services.filter(s => !s.added) — для диалога
```

### useScriptHub.js

Три новых API-метода: `addService()`, `removeService()`, `sortServices()`.

---

## 8. Список файлов

### Новые файлы

| Файл | Назначение |
|------|-----------|
| `Processors/Service/Add.php` | Процессор добавления |
| `Processors/Service/Remove.php` | Процессор удаления |
| `Processors/Service/Sort.php` | Процессор сортировки |
| `js/src/admin/AddServiceDialog.vue` | Диалог выбора сервиса |
| 16x `icon.svg` в папках сервисов | Иконки |

### Изменяемые файлы

| Файл | Что меняется |
|------|-------------|
| xPDO-схема + модель | Поле `added` |
| `AbstractService.php` | `getIconSvg()`, `toArray()` |
| `ServiceRegistry.php` | Сканирование папок |
| `GetList.php` | `added`, `iconSvg`, сортировка |
| `Update.php` | Авто-активация |
| `serviceStore.js` | Новые методы, computed |
| `useScriptHub.js` | Три API-метода |
| `AdminApp.vue` | Кнопка, empty state |
| `ServiceDashboard.vue` | Только added, drag & drop |
| `ServiceCard.vue` | draggable, iconSvg |
| `ServiceConfigPanel.vue` | Кнопка «Удалить» + confirm |
| 16x PHP сервисов | Переезд в папки |

---

## 9. Порядок реализации

1. **Миграция файлов** — переезд 16 сервисов в папки, обновление ServiceRegistry
2. **Иконки** — найти/создать 16 SVG, добавить `getIconSvg()` в AbstractService
3. **БД** — добавить поле `added`, обновить схему и модель
4. **Процессоры** — Add, Remove, Sort + изменения в GetList, Update
5. **Store + API** — новые методы в Pinia и composable
6. **AddServiceDialog** — диалог выбора
7. **Дашборд** — empty state, только added, drag & drop
8. **ServiceCard** — SVG иконки, draggable
9. **ServiceConfigPanel** — кнопка удаления, confirm
10. **Сборка + тест** — npm run build, проверка в браузере
