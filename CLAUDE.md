# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Development (watch mode)
npm start

# Production build
npm run build

# Linting
npm run lint:js
npm run lint:css

# Code formatting
npm run format

# Create distributable zip
npm run plugin-zip
```

There are no automated tests in this project.

## Architecture

This is a WordPress plugin that adds conditional visibility controls to existing Gutenberg blocks — it registers no new blocks of its own.

### How visibility is applied

The plugin operates in two layers:

1. **Editor (JS)**: [src/index.js](src/index.js) hooks into `blocks.registerBlockType` and `editor.BlockEdit` via `@wordpress/hooks` to inject a "Visibility Manager" panel into the block inspector sidebar for each enabled block. Attributes are prefixed `bvm*` (e.g. `bvmEnableVisibility`, `bvmUserRoles`, `bvmDateRange`). The list of blocks that show this panel is passed from PHP as `window.bvmEnabledBlocks` (a JS global).

2. **Frontend (PHP)**: [block-visibility-manager.php](block-visibility-manager.php) filters `render_block` to evaluate visibility conditions server-side. If time or date conditions aren't met, the block renders as an empty string. Device visibility (`hide-on-mobile`, `hide-on-tablet`, `hide-on-desktop`) is handled purely via CSS classes injected with `WP_HTML_Tag_Processor`, resolved by media queries in [src/style.scss](src/style.scss).

### Block enable/disable system

Admins can limit which blocks expose visibility controls via **Settings > Block Visibility** (registered in [includes/settings.php](includes/settings.php)). The enabled set is computed as:

```
all_registered_blocks − disabled_blocks (stored in WP option: block_visibility_manager_disabled_blocks)
```

The option stores the *disabled* list (not the enabled list). Default disabled blocks are defined in [includes/helpers.php](includes/helpers.php) (`block_visibility_manager_get_default_disabled_blocks`) and are set on plugin activation. The enabled list is then passed to the editor as `window.bvmEnabledBlocks`.

### File roles

| File | Purpose |
|---|---|
| `block-visibility-manager.php` | Plugin bootstrap: enqueues assets, runs `render_block` filter, loads includes |
| `includes/helpers.php` | PHP utilities: block grouping, role fetching, enabled/disabled block resolution |
| `includes/settings.php` | Admin settings page (renders the block enable/disable UI) |
| `src/index.js` | All editor JS: attribute registration + `InspectorControls` UI |
| `src/style.scss` | Frontend CSS: device-based media query breakpoints |
| `src/editor.scss` | Editor-only CSS overrides |
| `src/admin.css` | Admin settings page card grid styles |
| `uninstall.php` | Deletes `block_visibility_manager_disabled_blocks` option on uninstall |

### Key implementation details

- **Includes are loaded at plugin load time** (top-level `require_once`), not on `init`, so that helpers are available to the activation hook.
- **Time range** uses WordPress server time (`current_time('H:i')`); the `bvmTimeRange` attribute stores `from`/`to` as `"HH:MM"` strings. The PHP renderer validates them with `is_string()` + `preg_match('/^\d{2}:\d{2}$/', ...)` before comparing.
- **Date range** uses UTC+0. `new DateTime()` calls on attribute values are wrapped in `try/catch` and fail closed (hide the block) on malformed input.
- **User role hiding**: `bvmUserRoles` lists roles to *hide from*. `"guest"` is a synthetic role (non-logged-in users) handled in a separate branch before the WP roles check. Logged-out users skip `wp_get_current_user()` entirely.
- **Device hiding** never touches PHP — it only injects CSS classes via `WP_HTML_Tag_Processor` (guarded with `next_tag()`) and relies on `style-index.css` on the frontend. If no device classes are needed, `$block_content` is returned directly without touching the processor.
- **Settings nonces**: the save action uses nonce `bvm_save_settings`/field `bvm_save_nonce`; the reset action uses `bvm_reset_settings`/`bvm_reset_nonce`. Each is verified independently.
- The `render_block` filter exposes a `block_visibility_manager_should_render` filter hook for third-party override.
- Script dependencies are `wp-blocks`, `wp-element`, `wp-components`, `wp-block-editor`, `wp-compose`, `wp-hooks`, `wp-data`. Do not re-add `wp-edit-post` (deprecated in WP 6.6+).
- Build output goes to `build/` (compiled by `@wordpress/scripts`/webpack). The `build/` directory is committed.
