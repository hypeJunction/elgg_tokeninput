# elgg_tokeninput — Architecture (Elgg 5.x)

## Summary

Utility plugin providing a tokenizing autocomplete input for Elgg. Wraps the
jQuery Tokeninput library and exposes it as the `input/tokeninput` view so
other plugins can build pickers for users, groups, entities, or free tags.
The plugin has no persistent storage of its own — it renders inputs, wires
JS assets, and post-processes submitted form values via an action event.

Target version: **Elgg 5.x** (PHP 8.0+).

## Directory layout

```
elgg_tokeninput/
├── classes/hypeJunction/Tokeninput/
│   └── Bootstrap.php           # PluginBootstrap subclass — loads lib at load() time
├── lib/
│   └── tokeninput.php          # Entity/metadata export helpers, search functions,
│                               # page handler, and action event handler
├── views/default/
│   ├── input/tokeninput.php    # The input view itself
│   ├── tokeninput/
│   │   ├── require.php         # AMD require shim
│   │   ├── lib.js.php          # JS module
│   │   └── stylesheet.css.php  # Component CSS
│   ├── resources/tokeninput.php           # Route resource view
│   └── theme_sandbox/forms/elgg_tokeninput.php  # Theme sandbox example
├── composer.json               # Sole metadata source (Elgg 4.x+)
├── elgg-plugin.php             # Runtime config: bootstrap, events, views, routes
├── autoloader.php              # Composer autoloader bridge
└── docker/                     # Per-plugin Elgg 5.x test stack
```

## elgg-plugin.php

| Key | Contents |
|-----|----------|
| `plugin.name` | "Tokenizing Autocomplete" |
| `plugin.version` | `5.0.0` |
| `plugin.dependencies` | `search` (position: after) — Elgg core |
| `bootstrap` | `\hypeJunction\Tokeninput\Bootstrap` |
| `events` | `action:all` → `elgg_tokeninput_explode_field_values` @ priority 1 |
| `views` | Registers the bundled `jquery.tokeninput.min.js` as the `jquery.tokeninput.js` view |
| `routes` | `tokeninput` → `/tokeninput/{segments}` resolving to the `tokeninput` resource |
| `view_extensions` | Appends `tokeninput/stylesheet.css` to `elgg.css` and `admin.css`; appends `tokeninput/require` to `input/tokeninput`; adds a theme-sandbox form example |

No entities, subtypes, or database tables are registered.

## Events

Registered in `elgg-plugin.php` `'events'` section (Elgg 5.x unified event system):

| Event | Object type | Handler | Priority |
|-------|-------------|---------|----------|
| `action` | `all` | `elgg_tokeninput_explode_field_values` | 1 |

The handler inspects incoming action payloads and, for any field flagged
`autoexplode`, splits a comma-separated token string back into an array
before the action runs. This is how the input round-trips multi-value
tokens through Elgg actions that expect arrays.

Fired by lib functions (customizable via event handlers):

| Event | Object type | Default return |
|-------|-------------|---------------|
| `tokeninput:entity:export` | `$entity_type` | export array |
| `search` | `user\|group\|object` | `[]` |

## Routes

| Route name | Path | Resource |
|------------|------|----------|
| `tokeninput` | `/tokeninput/{segments}` | `tokeninput` |

The resource view calls `elgg_tokeninput_page_handler()` which dispatches to
configurable search callbacks (default: `elgg_tokeninput_search_all`). Callers
can pass a custom `callback` query parameter signed with HMAC to use their own
search function.

## Dependencies

- **Elgg core** `^5.0`
- **Core plugin**: `search` (loaded as a dep before this plugin)
- **PHP**: `>=8.0`
- **jQuery Tokeninput** (bower asset `bower-asset/jquery-tokeninput ~1.7.0`)

## Migration notes — 4.x → 5.x

- `composer.json` bumped to PHP ≥ 8.0 and `elgg/elgg ^5.0`.
- Docker test stack upgraded: `php:8.1-apache`, `mysql:8.0`, `elgg/elgg ~5.1.0`.
- `'hooks'` key renamed to `'events'` in `elgg-plugin.php` (Elgg 5.x unified event system).
- `elgg_register_plugin_hook_handler()` replaced by declarative `'events'` section.
- All `elgg_trigger_plugin_hook()` calls replaced with `elgg_trigger_event_results()`.
- `\Elgg\Hook` callback signature updated to `\Elgg\Event` in `elgg_tokeninput_explode_field_values`.
- `lib/tokeninput.php` is now loaded at `Bootstrap::load()` (not `init()`) so that the
  action handler function is defined before the events system resolves callables.
- Two functions missing from the 4.x migration have been added:
  - `elgg_tokeninput_page_handler()` — AJAX search endpoint dispatcher
  - `elgg_tokeninput_explode_field_values()` — action event handler
- PHP 8.0 strict undefined-variable fixes in `input/tokeninput.php`:
  `$limit` and `$query` initialized before conditional assignment.
- `$vars['multiple']` access hardened with `elgg_extract()` in `input/tokeninput.php`.
- DOM element ID generation changed from `md5(microtime().rand())` to `bin2hex(random_bytes(5))`.
- Integration test updated: `ElggSession::setLoggedInUser()` → `_elgg_services()->session_manager->setLoggedInUser()` (Elgg 5.x API).
- PHPUnit test suite: 16 tests, 127 assertions — all pass on Elgg 5.1.x.
