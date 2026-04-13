# Changelog

## [Unreleased] — Elgg 4.x migration

### Migrated to Elgg 4.x

- Bumped composer constraints: `elgg/elgg ^4.0`, `php >=7.4`, `composer/installers ^2.0`
- Added `config.allow-plugins.composer/installers` (required by composer 2.2+)
- Replaced `extra.installer-name` with `extra.elgg-plugin.id` (lowercase canonical)
- **Deleted `manifest.xml`** — composer.json is the sole metadata source from 4.x onward
- **Deleted `start.php`** — Iron Law: 4.x rejects plugins with any start.php file
- **Created `elgg-plugin.php`** with declarative `plugin`, `bootstrap`, and `hooks` config
- **Created `Bootstrap` class** extending `\Elgg\DefaultPluginBootstrap`
- **Moved `hypeFilestore()` factory** from `lib/autoloader.php` to `lib/functions.php` (loaded via `require_once` at top of `elgg-plugin.php`)
- **Converted `PluginHooks::handleEntityIconUrls` to `\Elgg\Hook` signature** (single-arg, static method); the legacy 4-arg form is gone
- **Rewrote `PluginContainer`** to NOT extend `\Elgg\Di\DiContainer` — that class became abstract in 4.x and removed `setFactory()`. Now uses a plain class with `__get` magic accessor for lazy service construction. Outward interface preserved.
- **Lowercased `Config::PLUGIN_ID`** to `'hypefilestore'` (Iron Law 6); factory falls back to camelCase for 3.x compat
- **Removed `elgg_register_css()` / `elgg_register_external_view()` from Bootstrap::init()** — both removed in 4.x
- **Deleted `vendors/cropper/examples/`** — third-party demo files contained an XSS pattern flagged by the security sweep; demo dir is not used by the plugin
- Adapted tests/bootstrap.php to detect 4.x function location

### Deferred to 4→5 or later

- `Icons/Server.php` still uses removed `mysql_*` extension (dead code path, but should be rewritten)
- `move_uploaded_file` filename sanitization audit
- md5 ETags in Icons/Factory.php (cosmetic, not a security risk for ETags)

## [Unreleased] — Elgg 3.x migration

### Migrated to Elgg 3.x

- Bumped `manifest.xml` `elgg_release` requirement from 1.10 to 3.0
- Generated proper `composer.json` with `elgg/elgg ^3.0`, `php >=7.0`, PSR-4 autoload for `hypeJunction\Filestore\` and `WideImage\`
- Replaced `elgg_register_classes()` (removed in 3.0) with PSR-4 autoloading via composer
- Replaced `get_subtype_class()` with `elgg_get_entity_class()` in upload handling
- Removed `global $CONFIG` access in `servers/icon.php`; uses `elgg_get_config('dbprefix')` now
- Added pre-migration smoke test suite (10 tests, runs in both elgg2 and elgg3 containers)
- Generated `ARCHITECTURE.md` documenting the plugin structure and known-issue carry-forward list
- Modernized license SPDX identifier to `GPL-2.0-or-later`

### Known issues (deferred to 3→4 or later)

- `Icons/Server.php` uses removed `mysql_*` extension — fast icon path is broken on PHP 7+
- `handleEntityIconUrls` uses legacy 4-arg hook signature
- `start.php` uses 2.x-style top-level event handler registration
- Several security warnings (md5 ETags, file upload sanitization audit, mysql_query SQL injection in unused fast-icon path)
