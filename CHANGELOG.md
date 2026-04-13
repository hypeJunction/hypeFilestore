# Changelog

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
