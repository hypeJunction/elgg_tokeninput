# elgg_tokeninput — Architecture (Elgg 4.x)

## Summary

Utility plugin providing a tokenizing autocomplete input for Elgg. Wraps the
jQuery Tokeninput library and exposes it as the `input/tokeninput` view so
other plugins can build pickers for users, groups, entities, or free tags.
The plugin has no persistent storage of its own — it renders inputs, wires
JS assets, and post-processes submitted form values via an action hook.

Target version: **Elgg 4.x** (PHP 7.4+).

## Directory layout

```
elgg_tokeninput/
├── classes/hypeJunction/Tokeninput/
│   └── Bootstrap.php           # PluginBootstrap subclass
├── lib/
│   └── tokeninput.php          # Entity/metadata export helpers + action handler
├── views/default/
│   ├── input/tokeninput.php    # The input view itself
│   ├── tokeninput/
│   │   ├── require.php         # AMD require shim
│   │   ├── lib.js.php          # JS module
│   │   └── stylesheet.css.php  # Component CSS
│   ├── resources/tokeninput.php           # Route resource
│   └── forms/admin/theme_sandbox/... (theme sandbox example)
├── composer.json               # Sole metadata source (Elgg 4.x)
├── elgg-plugin.php             # Runtime config: bootstrap, views, routes
├── autoloader.php              # Composer autoloader bridge
└── docker/                     # Per-plugin Elgg 4.x test stack
```

## elgg-plugin.php

| Key | Contents |
|-----|----------|
| `plugin.name` | "Tokenizing Autocomplete" |
| `plugin.version` | `5.0.0` |
| `plugin.dependencies` | `search` (position: after) — Elgg core |
| `bootstrap` | `\hypeJunction\Tokeninput\Bootstrap` |
| `views` | Registers the bundled `jquery.tokeninput.min.js` as the `jquery.tokeninput.js` view |
| `routes` | `tokeninput` → `/tokeninput/{segments}` resolving to the `tokeninput` resource |
| `view_extensions` | Appends `tokeninput/stylesheet.css` to `elgg.css` and `admin.css`; appends `tokeninput/require` to `input/tokeninput`; adds a theme-sandbox form example |

No entities, subtypes, or database tables are registered.

## Hooks and events

Registered in `Bootstrap::init()`:

| Kind | Name | Handler | Priority |
|------|------|---------|----------|
| hook | `action:all` | `elgg_tokeninput_explode_field_values` | 1 |

The handler inspects incoming action payloads and, for any field flagged
`autoexplode`, splits a comma-separated token string back into an array
before the action runs. This is how the input round-trips multi-value
tokens through Elgg actions that expect arrays.

## Routes

| Route name | Path | Resource |
|------------|------|----------|
| `tokeninput` | `/tokeninput/{segments}` | `tokeninput` |

The resource view takes `segments` and dispatches to per-type search
callbacks (users, groups, entities, tags). See `views/default/resources/tokeninput.php`.

## Dependencies

- **Elgg core** `^4.0`
- **Core plugin**: `search` (used as a fallback backend for entity lookups)
- **PHP**: `>=7.4`
- **jQuery Tokeninput** (bower asset `bower-asset/jquery-tokeninput ~1.7.0`)

## Migration notes — 3.x → 4.x

- `manifest.xml` removed; `composer.json` is now the sole metadata source.
- `plugin.dependencies` declared in `elgg-plugin.php` (search, position: after).
- `composer.json` bumped to PHP ≥ 7.4 and `composer/installers ^2.0`.
- `config.allow-plugins` declared (required by Composer 2.2+ for the plugin installer).
- License SPDX id normalized to `GPL-2.0-or-later`.
- PHPUnit integration test suite and Playwright smoke added for regression coverage.
- Per-plugin Docker stack under `docker/` provisions Elgg 4.x, symlinks core
  plugins from `vendor/elgg/elgg/mod/`, activates `search` as a dep, then
  positions and activates `elgg_tokeninput` at the end of the load order.

## Known issues / follow-ups

- `views/default/input/tokeninput.php:23` generates the DOM id via
  `substr(md5(microtime() . rand()), 0, 10)`. This is a DOM-uniqueness use of
  `md5`, not a security one — the security sweep flags it as a warning only.
  Replacing with `bin2hex(random_bytes(5))` or `elgg_view('input/…', …)`'s
  own id generation is safe, but out of scope for the 4.x migration.
- `Bootstrap::init()` registers `elgg_tokeninput_explode_field_values`
  before the lib file that declares it is required. The call order is
  correct (require runs first) but PHPUnit prints a "Handler is not
  callable" warning during the boot probe — cosmetic only, tests still pass.
