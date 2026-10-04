# AGENTS.md

**scriptHub** is a MODX Revolution 3 extra: one hub for external scripts
(16 services: analytics, ad pixels, chats, lead generation). Vue 3 admin UI
via VueTools, PSR-4 namespace `RenderRoom\ScriptHub`, PHP 8.1+.

It is not a standalone app: it runs inside a MODX installation, so PHP
scripts are executed in a MODX environment (the owner uses Docker; run PHP as
the web-server user, e.g. `www-data`).

## Commands

```bash
# Vue dev (watch)
cd assets/components/scripthub/js && npm install && npm run dev

# Vue production build
# output: assets/components/scripthub/mgr/vue-dist/scripthub-admin.min.js
cd assets/components/scripthub/js && npm run build

# Inside the MODX environment:
php core/components/scripthub/build/build.schema.php  # regenerate xPDO model
php core/components/scripthub/build/install.php       # register the component
```

No tests, no lint. Smoke check is manual in the MODX manager.

## Architecture

Two independent request paths:

1. Frontend injection: MODX `OnMODXInit` → `bootstrap.php` (autoload,
   `addPackage`, DI) → `OnWebPagePrerender` → `plugin.scripthub.php` →
   `ScriptRenderer` builds HTML for enabled services and inserts it before
   `</head>`, after `<body>`, before `</body>`.
2. Admin API: Vue (`assets/components/scripthub/js/src/admin/`) →
   `assets/components/scripthub/connector.php?action=mgr/service/<name>` →
   `src/Processors/Service/<Name>.php` → model `\scripthub\ScriptHubService`.

- **`AbstractService` + `ServiceRegistry`** (`src/Services/`): each service is
  a subclass in `Services/<Category>/<Name>/<Name>.php` (categories
  `Analytics`, `Pixels`, `Chats`, `LeadGen`). `ServiceRegistry::discover()`
  finds them automatically and hydrates `enabled/added/config/position` from
  the DB. No manual registration.
- **Service self-description:** `getKey()`, `getName()`, `getCategory()`,
  `getFields()` (`FieldType` enum), `getPosition()` (`InjectionPosition`:
  HEAD / BODY_END / AFTER_BODY_OPEN), `render()`, optional `renderNoscript()`.
  Icon: `icon.svg` next to the PHP file or `getIcon()` with a PrimeIcons class.
- **State:** one table `modx_scripthub_services` (`service_key`, `added`,
  `enabled`, `config` JSON, `position`). Only user config is stored, no logic.
- **Processors** (`src/Processors/Service/`): Add, Update, Get, GetList,
  Toggle, Remove, Sort, RefreshAsset; each checks `checkPolicy('settings')`.
  RefreshAsset downloads the self-hosted Yandex.Metrika `tag.js`.
- **Bootstrap:** registers the PSR-4 autoloader (if no `vendor/autoload`),
  `$modx->addPackage('scripthub', model/)`, service `scripthub` in
  `$modx->services`; guarded by `SCRIPTHUB_BOOTSTRAPPED`.

## Gotchas

1. No `declare(strict_types=1)` in `plugin.scripthub.php`: MODX runs plugin
   content via `eval()`, strict_types breaks it. Everywhere else it is required.
2. `build/install.php` registers the component in an existing MODX; it is not
   a transport builder. The transport package is built by
   `_build/build.transport.php`.
3. A service is injected only if both `added` and `enabled` are true
   (`ServiceRegistry::getEnabled()`). Update/Toggle/Sort never create rows;
   rows are created only by Add (protects against direct-POST bypass).
4. Vue sources live only in this repo. Never edit the minified bundle
   `mgr/vue-dist/scripthub-admin.min.js`; rebuild with `npm run build`.
5. Lexicon has two locales, en and ru: keep them in sync, no hard-coded
   Russian strings in server-side validation.

## Adding a service

One file `core/components/scripthub/src/Services/<Category>/<Name>/<Name>.php`:

```php
<?php
declare(strict_types=1);
namespace RenderRoom\ScriptHub\Services\Pixels\MyPixel;

use RenderRoom\ScriptHub\Services\{AbstractService, ServiceCategory, FieldType, InjectionPosition};

class MyPixel extends AbstractService
{
    public function getKey(): string { return 'my-pixel'; }
    public function getName(): string { return 'My Pixel'; }
    public function getCategory(): ServiceCategory { return ServiceCategory::PIXELS; }
    public function getPosition(): InjectionPosition { return InjectionPosition::HEAD; }
    public function getFields(): array {
        return [['key' => 'pixel_id', 'type' => FieldType::TEXT->value, 'required' => true]];
    }
    public function render(): string {
        $id = $this->jsEncode($this->sanitizeId($this->cfg('pixel_id')));
        return "<script>/* ... {$id} ... */</script>";
    }
}
```

Put `icon.svg` next to it, rebuild Vue if needed, check it in the dashboard.

Security: pass all user input through the `AbstractService` helpers:
`sanitizeId()`, `sanitizeUrl()` (with a host allowlist where possible),
`sanitizeHost()`, `jsEncode()` for JS literals, `htmlAttr()` for attributes.
