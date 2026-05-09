## [6.0.0] — Elgg 6.x migration (2026-05-09)

- Migrated to Elgg 6.x (5.x → 6.x). Requires PHP 8.0+.
- Bumped `elgg/elgg` constraint to `^6.0`.
- AMD JS removed: `components/tokeninput.js` converted from AMD `define()` to ES module.
- `elgg.trigger_hook()` → `trigger()` from `elgg/hooks` in JS formatters.
- `elgg.echo()` → `echo()` from `elgg/i18n`.
- Inline `require(['tokeninput/lib'], ...)` in `tokeninput/require.php` → `elgg_import_esm()`.
- Inline AMD `require(['jquery-ui/widgets/sortable'], ...)` → dynamic `import()`.
- Docker stack upgraded to Elgg 6.x, PHPUnit ~10.5.

---

<a name="5.1.0"></a>
## 5.1.0 (2026-04-20)

### Elgg 5.x migration

* Target Elgg `^5.0` (PHP >= 8.0).
* `'hooks'` key renamed to `'events'` in `elgg-plugin.php` (Elgg 5.x unified event system).
* `elgg_register_plugin_hook_handler()` replaced by declarative `'events'` entry.
* All `elgg_trigger_plugin_hook()` calls replaced with `elgg_trigger_event_results()`.
* Action event handler signature updated to `\Elgg\Event $event`.
* Lib loaded at `Bootstrap::load()` so event handler is callable at registration time.
* Added missing functions: `elgg_tokeninput_page_handler()` and `elgg_tokeninput_explode_field_values()`.
* PHP 8.0 strict fixes in `input/tokeninput.php` (undefined variable initialization).
* DOM element ID generation changed from `md5()` to `bin2hex(random_bytes(5))`.
* Docker test stack upgraded to PHP 8.1 / MySQL 8.0 / Elgg 5.1.x.
* Integration test updated for Elgg 5.x session API.

<a name="5.0.0"></a>
## 5.0.0 (2026-04-14)

### Elgg 4.x migration

* Migrated plugin to Elgg 4.x (via 3.x). Target PHP >= 7.4.
* `manifest.xml` removed — `composer.json` is now the sole metadata source.
* `plugin.dependencies` declared in `elgg-plugin.php` (`search`, position: after).
* `composer.json` bumped to `composer/installers ^2.0` with `config.allow-plugins` block.
* License SPDX id normalized to `GPL-2.0-or-later`.
* Added PHPUnit integration test suite (16 tests, 127 assertions) and Playwright smoke.
* Added per-plugin Docker test stack under `docker/` (Elgg 4.x + MySQL + Playwright via `--profile test`).
* `ARCHITECTURE.md` added.

<a name="4.1.3"></a>
## [4.1.3](https://github.com/hypeJunction/elgg_tokeninput/compare/4.1.2...v4.1.3) (2016-12-16)


### Bug Fixes

* **css:** dropdown z-index should be higher than colorbox value ([f6890bb](https://github.com/hypeJunction/elgg_tokeninput/commit/f6890bb))



<a name="4.1.2"></a>
## [4.1.2](https://github.com/hypeJunction/elgg_tokeninput/compare/4.1.1...v4.1.2) (2016-08-03)


### Bug Fixes

* **export:** arbitrary strings should be exported as metadata ([0b2c937](https://github.com/hypeJunction/elgg_tokeninput/commit/0b2c937))



<a name="4.1.1"></a>
## [4.1.1](https://github.com/hypeJunction/elgg_tokeninput/compare/4.1.0...v4.1.1) (2016-06-16)


### Bug Fixes

* **input:** do not explode input/autocomplete values ([60637ba](https://github.com/hypeJunction/elgg_tokeninput/commit/60637ba)), closes [#7](https://github.com/hypeJunction/elgg_tokeninput/issues/7)
* **input:** userpicker input behaviour now corresponds to core ([82258de](https://github.com/hypeJunction/elgg_tokeninput/commit/82258de)), closes [#6](https://github.com/hypeJunction/elgg_tokeninput/issues/6)
* **views:** fix variable name in views.php ([768a19c](https://github.com/hypeJunction/elgg_tokeninput/commit/768a19c))



<a name="4.1.0"></a>
# [4.1.0](https://github.com/hypeJunction/elgg_tokeninput/compare/4.0.2...v4.1.0) (2016-02-09)


### Features

* **css:** slight restyle ([7e0801f](https://github.com/hypeJunction/elgg_tokeninput/commit/7e0801f))
* **tokens:** tokens can now be sorted ([025a9c8](https://github.com/hypeJunction/elgg_tokeninput/commit/025a9c8))
* **views:** better instantiation and BC ([56b1f44](https://github.com/hypeJunction/elgg_tokeninput/commit/56b1f44))



<a name="4.0.2"></a>
## [4.0.2](https://github.com/hypeJunction/elgg_tokeninput/compare/3.3.2...v4.0.2) (2016-01-24)




<a name="4.0.1"></a>
## [4.0.1](https://github.com/hypeJunction/elgg_tokeninput/compare/4.0.0...4.0.1) (2015-10-08)


### Bug Fixes

* **views:** fix typo in path name ([0468a49](https://github.com/hypeJunction/elgg_tokeninput/commit/0468a49))



<a name="4.0.0"></a>
# [4.0.0](https://github.com/hypeJunction/elgg_tokeninput/compare/3.3.0...4.0.0) (2015-10-08)


### Bug Fixes

* **deps:** fix autoloading ([6012e93](https://github.com/hypeJunction/elgg_tokeninput/commit/6012e93))
* **js:** fix boostraping on ajax requests ([025ea4e](https://github.com/hypeJunction/elgg_tokeninput/commit/025ea4e))



<a name="3.3.2"></a>
## [3.3.2](https://github.com/hypeJunction/elgg_tokeninput/compare/3.3.1...v3.3.2) (2016-01-24)




<a name="3.3.1"></a>
## [3.3.1](https://github.com/hypeJunction/elgg_tokeninput/compare/3.3.0...v3.3.1) (2016-01-24)


### Features

* **releases:** add release info ([f347381](https://github.com/hypeJunction/elgg_tokeninput/commit/f347381))



