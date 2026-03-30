---
name: composer-plugin-setup
description: Sets up or updates composer.json for a myadmin-plugin type package with PSR-4 autoload, detain/myadmin-plugin-installer, and symfony/event-dispatcher. Use when creating a new plugin package or updating plugin dependencies. Trigger phrases: 'new myadmin plugin', 'set up composer', 'add plugin-installer'. Do NOT use for non-plugin packages or for updating the parent MyAdmin system's composer.json.
---
# composer-plugin-setup

## Critical

- `"type"` MUST be `"myadmin-plugin"` — the installer uses this to place the package correctly.
- The myadmin plugin installer package MUST be in `require` (not `require-dev`) at `dev-master`.
- `minimum-stability` MUST be `"dev"` in `config` (installer requires it).
- Namespace convention: `Detain\MyAdmin{PluginName}\` → `src/`, tests under `Detain\MyAdmin{PluginName}\Tests\` → `tests/`.
- Use tabs for indentation in `composer.json` (matches `.scrutinizer.yml` coding style).

## Instructions

1. **Set package identity fields.** Use `detain/myadmin-{plugin-name}` format as `name` — for example, `detain/myadmin-kayako-chat`. Set `"type": "myadmin-plugin"`. Provide a one-line `description`. Set `"license": "LGPL-2.1-only"`.
   ```json
   {
   	"name": "detain/myadmin-kayako-chat",
   	"type": "myadmin-plugin",
   	"description": "{Plugin} handling plugin for MyAdmin",
   	"license": "LGPL-2.1-only"
   }
   ```
   Verify the slug matches the directory name under `vendor/detain/` before proceeding.

2. **Add author block.** Use the project-standard author entry:
   ```json
   	"authors": [
   		{
   			"name": "Joe Huss",
   			"homepage": "https:\/\/my.interserver.net\/"
   		}
   	],
   ```

3. **Set config block** with `bin-dir` and `minimum-stability`:
   ```json
   	"config": {
   		"bin-dir": "vendor\/bin",
   		"minimum-stability": "dev"
   	},
   ```
   Verify `minimum-stability` is `"dev"` — omitting it causes install failure with `dev-master` deps.

4. **Populate `require`.** Always include these three; add `ext-{name}: "*"` for any PHP extensions the plugin needs:
   ```json
   	"require": {
   		"php": ">=5.0.0",
   		"ext-soap": "*",
   		"symfony/event-dispatcher": "^5.0@stable",
   		"detain/myadmin-plugin-installer": "dev-master"
   	},
   ```
   Verify the plugin installer package is present — without it the package won't be installed into MyAdmin.

5. **Populate `require-dev`:**
   ```json
   	"require-dev": {
   		"phpunit/phpunit": "^9.6"
   	},
   ```

6. **Set PSR-4 autoload blocks.** Derive namespace from package slug (kebab segments → PascalCase concatenated):
   - `myadmin-kayako-chat` → `Detain\MyAdminKayakoChat\`
   ```json
   	"autoload": {
   		"psr-4": {
   			"Detain\\MyAdmin{PluginName}\\": "src/"
   		}
   	},
   	"autoload-dev": {
   		"psr-4": {
   			"Detain\\MyAdmin{PluginName}\\Tests\\": "tests/"
   		}
   	}
   ```
   Verify `src/Plugin.php` declares `namespace Detain\MyAdmin{PluginName};` to match before proceeding.

7. **Run `composer install` and confirm no errors:**
   ```bash
   composer install
   php -l src/Plugin.php && php -l tests/PluginTest.php
   ```
   Both must exit 0. This step uses the `autoload` output from Step 6.

## Examples

**User says:** "Set up composer.json for a new plugin called myadmin-whmcs-billing"

**Actions taken:**
- `name` → `detain/myadmin-whmcs-billing`
- Namespace → `Detain\MyAdminWhmcsBilling\` → `src/`, `Detain\MyAdminWhmcsBilling\Tests\` → `tests/`
- `require` includes `symfony/event-dispatcher: ^5.0@stable` and the myadmin plugin installer at `dev-master`
- `config.minimum-stability` set to `dev`
- Confirm `src/Plugin.php` opens with `namespace Detain\MyAdminWhmcsBilling;`

**Result:** `composer install` succeeds; PHPUnit finds and runs `tests/PluginTest.php`.

## Common Issues

- **Package not found error for myadmin-plugin-installer**: `minimum-stability` is missing or set to `stable`. Fix: add `"minimum-stability": "dev"` inside the `config` block.
- **`Class 'Detain\MyAdminFoo\Plugin' not found`**: PSR-4 namespace in `composer.json` doesn't match `namespace` declaration in `src/Plugin.php`. Fix: run `composer dump-autoload` and check both match exactly (case-sensitive).
- **`symfony/event-dispatcher` version conflict**: Using `^4.x` instead of `^5.0@stable`. Fix: set `"symfony/event-dispatcher": "^5.0@stable"`.
- **Tests not discovered**: `autoload-dev` block missing or pointing to wrong directory. Fix: confirm `tests/` directory exists and `autoload-dev` maps the `\Tests\` sub-namespace to `tests/`.
- **PHPUnit binary not found after install**: `require-dev` omitted. Fix: add `"phpunit/phpunit": "^9.6"` under `require-dev` and re-run `composer install`.
