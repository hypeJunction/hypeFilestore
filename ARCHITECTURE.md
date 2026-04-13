# hypeFilestore — Architecture (Elgg 3.x)

File and image handling utilities for Elgg. Provides icon generation, file uploads, and a fast HMAC-signed icon serving path.

## Plugin summary

- **Composer name:** `hypejunction/hypefilestore`
- **Plugin id:** `hypefilestore` (lowercase canonical from the 2→3 migration; mod/ dir still `hypeFilestore` for 3.x compatibility — will lowercase in 3→4)
- **Manifest:** `manifest.xml` with `<elgg_release>3.0</elgg_release>` (kept in place for 3.x; deleted in 3→4)
- **PHP minimum:** 7.0
- **Direct deps:** `ext-json`, `ext-xml`, `ext-curl`, `ext-gd`, `composer/installers ~1.0`
- **Vendored libs:** `vendors/WideImage/` (PSR-4 namespace `WideImage\`)

## Directory structure

```
hypefilestore/
├── classes/hypeJunction/Filestore/        PSR-4: hypeJunction\Filestore\
│   ├── Config/Config.php                  Plugin config wrapper
│   ├── CoverHandler.php                   Cover image handler
│   ├── Di/PluginContainer.php             Plugin DI container (extends \Elgg\Di\DiContainer)
│   ├── Handlers/Image.php                 Image manipulation (uses WideImage)
│   ├── Handlers/Uploader.php              File upload handler
│   ├── Handlers/Uploader/Upload.php       Single-upload value object
│   ├── IconHandler.php                    Icon URL handler
│   ├── Icons/Factory.php                  Icon factory
│   ├── Icons/Server.php                   Fast icon serving (legacy mysql_query path)
│   ├── Listeners/PluginHooks.php          Plugin hook registration + handlers
│   └── UploadHandler.php                  Upload action handler
├── lib/autoloader.php                     Defines hypeFilestore() global factory; PSR-4 replaces elgg_register_classes
├── servers/icon.php                       Fast icon serving entry point
├── start.php                              Init handler registration (transitional 2.x style, still supported in 3.x)
├── manifest.xml                           Plugin metadata (3.x reads it; 4.x will delete it)
├── composer.json                          PSR-4 autoload + 3.x require constraints
├── tests/                                 PHPUnit smoke tests
└── vendors/WideImage/                     Vendored PSR-4 image library
```

## Hooks registered

| Event | Type | Handler |
|-------|------|---------|
| `entity:icon:url` | `all` | `hypeJunction\Filestore\Listeners\PluginHooks::handleEntityIconUrls` — routes icon URL requests through the fast HMAC server |

## Entities

None registered. The plugin operates on existing `\ElggFile` entities.

## Routes

None. The fast icon serving endpoint at `servers/icon.php` is reached via direct URL (legacy pattern — should become a route in a future revision).

## Services (DI container)

`hypeFilestore()` returns a singleton `PluginContainer` (extends `\Elgg\Di\DiContainer`):

- `config` — `\hypeJunction\Filestore\Config\Config`
- `hooks` — `\hypeJunction\Filestore\Listeners\PluginHooks`
- `uploader` — `\hypeJunction\Filestore\Handlers\Uploader`
- `iconFactory` — `\hypeJunction\Filestore\Icons\Factory`

## Dependencies

No declared plugin-to-plugin dependencies. Other plugins (hypeAttachments, hypeWall) that use file handling have historically called into `hypeFilestore()->iconFactory` at runtime.

## Migration notes (2.x → 3.x)

Applied 2026-04-13.

- Manifest version bumped 1.10 → 3.0 (automated rule)
- `elgg_register_classes()` calls removed from `lib/autoloader.php` (automated); replaced with PSR-4 autoload in `composer.json` (manual — automated rule does not synthesize the replacement)
- `WideImage\` namespace registered as PSR-4 against `vendors/WideImage/lib/WideImage/`
- `get_subtype_class()` → `elgg_get_entity_class()` in `Handlers/Uploader/Upload.php` (automated)
- `global $CONFIG` removed from `servers/icon.php`; `$CONFIG->dbprefix` → `elgg_get_config('dbprefix')` (automated)
- Custom `composer.json` written: added `elgg/elgg ^3.0`, bumped php to `>=7.0`, added `ext-gd`, registered PSR-4 autoload, modernized SPDX license

### Known issues carried forward (track in 3→4 or later)

- `Icons/Server.php` uses the removed `mysql_*` PHP extension — the fast icon path will fatal on PHP 7+ but is reachable only via direct URL (`servers/icon.php`). Production has been masking this with the slow path. Must be rewritten before the fast path can be used again.
- `handleEntityIconUrls` still uses the legacy 4-arg hook signature `($hook, $type, $return, $params)` — works in 3.x, will need conversion to `\Elgg\Hook` in 3→4.
- `start.php` still uses the 2.x top-level `elgg_register_event_handler` pattern — works in 3.x via transitional support, must become a closure-return or `elgg-plugin.php` Bootstrap class in 3→4.
- Several security warnings flagged in pre-merge sweep: `move_uploaded_file` without sanitization audit, `md5()` for ETags (cosmetic), and the `mysql_query()` SQL-injection path noted above.
- One LLM-rule blocking warning in `vendors/cropper/examples/crop-avatar/crop-avatar.php` (third-party demo file, not used by the plugin) — exclude from migration scans or delete the demo dir in 3→4 cleanup.

## Tests

`tests/phpunit/smoke/PluginSmokeTest.php` — 10 smoke tests covering plugin load surface (factory function, DI container, services, hook handler callable). Bootstrap supports both 2.x and 3.x environments. Run via:

```bash
docker exec elgg3-elgg-1 vendor/bin/phpunit --configuration mod/hypeFilestore/tests/phpunit.xml --no-coverage
```

Pre-migration baseline: 10/11 in elgg2, 10/11 in elgg3 (post-migration). Both passing.
