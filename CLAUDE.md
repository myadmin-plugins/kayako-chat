# MyAdmin Kayako Chat Plugin

A `myadmin-plugin` package integrating Kayako Live Chat into MyAdmin via Symfony EventDispatcher.

## Commands

```bash
composer install                        # install deps including phpunit/phpunit ^9.6
vendor/bin/phpunit                      # run all tests
vendor/bin/phpunit tests/ -v            # verbose
vendor/bin/phpunit --coverage-clover coverage.xml --whitelist src/  # with coverage
```

## Architecture

- **Plugin class**: `src/Plugin.php` · namespace `Detain\MyAdminKayakoChat` · all methods static
- **Tests**: `tests/PluginTest.php` · namespace `Detain\MyAdminKayakoChat\Tests` · uses `ReflectionClass`
- **Autoload**: PSR-4 `Detain\MyAdminKayakoChat\` → `src/` · dev: `Tests\` → `tests/`
- **Events**: `Symfony\Component\EventDispatcher\GenericEvent` · subject accessed via `$event->getSubject()`
- **CI/CD**: `.github/` contains workflows for automated testing and pull request validation
- **IDE Config**: `.idea/` contains `inspectionProfiles/`, `deployment.xml`, `encodings.xml` for JetBrains IDE settings

## Plugin Pattern

`src/Plugin.php` exposes four static event handlers:

```php
public static function getHooks(): array          // returns event→callable map
public static function getMenu(GenericEvent $event)       // admin menu integration
public static function getRequirements(GenericEvent $event) // registers class/file requirements
public static function getSettings(GenericEvent $event)   // settings subsystem
```

`getRequirements` may call `$loader->add_requirement($name, $path)`, but this package
currently registers nothing. It used to register four paths — `class.Kayako` at
`src/Kayako.php` and three abuse helpers at `src/abuse.inc.php` — and neither file has ever
existed here. Any path you add must resolve: `include/tf.php` `require_once`s it with no
`file_exists` guard, so a wrong path is a fatal, not a warning.

## Lint & Style

```bash
php -l src/Plugin.php && php -l tests/PluginTest.php   # syntax check both files
```

## Conventions

- Static properties: `$name`, `$description`, `$help`, `$type = 'plugin'` — all `public static string`
- `getHooks()` returns `[]` (no hooks registered currently)
- Admin ACL check pattern: `$GLOBALS['tf']->ima == 'admin'` → `has_acl('client_billing')`
- Tabs for indentation (see `.scrutinizer.yml` coding style)
- No PDO — this plugin defers DB access to parent MyAdmin system

## Testing Conventions

- Extend `PHPUnit\Framework\TestCase`
- Use `ReflectionClass` to assert method/property visibility and static modifiers
- Test method names: `testCanBeInstantiated`, `testGetHooksReturnsArray`, `testGetMenuMethodSignature`
- Anonymous class pattern for mock loader in `testGetRequirementsRegistersOnlyFilesThatExist`
  (which replaced `testGetRequirementsCallsAddRequirement` and
  `testGetRequirementsRegistersCorrectPaths` — those asserted the four dead registrations
  were present and spelled a certain way, never that their files existed, so they locked the
  bug in place instead of catching it)
- Assert exact static property values: `assertSame('Kayako Plugin', Plugin::$name)`

## Dependencies

- `php >= 5.0.0` · `ext-soap` · `symfony/event-dispatcher ^5.0@stable`
- `detain/myadmin-plugin-installer dev-master`
- Dev: `phpunit/phpunit ^9.6`

## Plugin contract harness

This package is on the shared contract harness from `detain/myadmin-plugin-installer`.
`tests/ContractTest.php` is **generated** — run `composer myadmin:scaffold-tests` (add
`--force --write` to re-emit it), never hand-edit it.

The harness **executes** the plugin: it defines the bare constants the class body references
and then calls `getHooks()`, `getSettings()`, `getMenu()`, `apiRegister()` and — for
`type=service` packages — the activate/deactivate/change-ip/queue handlers, for real.

**So do not write reflection-only tests for the plugin class.** Asserting a handler exists,
is public, is static and takes one parameter passes whether or not the handler works; three
production bugs in this fleet were sitting behind assertions of exactly that shape. Older
guidance in this repo that says those methods must not be called predates the harness.

The harness is **additive**: it runs alongside this package's existing tests, and nothing is
deleted to make room for it. Run the whole suite, never `--filter ContractTest` alone — the
contract class primes constants and calls `register_module()`, neither of which can be undone.

See the `plugin-contract-tests` skill for the full workflow, and `docs/testing-harness.md` in
the installer.

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
