---
name: plugin-event-handler
description: Adds a new static event handler method to `src/Plugin.php` following the `GenericEvent` pattern. Use when user says 'add event handler', 'new hook', 'add to getHooks', or needs to extend plugin functionality. Handles `getSubject()` extraction, ACL checks, and `getHooks()` registration. Do NOT use for modifying existing handlers or creating new plugin classes.
---
# plugin-event-handler

## Critical

- All handler methods MUST be `public static` — never instance methods
- Parameter type hint MUST be `\Symfony\Component\EventDispatcher\GenericEvent $event`
- Subject is ALWAYS extracted via `$event->getSubject()` — never access `$event` properties directly
- ACL checks require `$GLOBALS['tf']->ima == 'admin'` guard BEFORE calling `has_acl()`; call `function_requirements('has_acl')` first
- Use tabs for indentation (not spaces) — enforced by `.scrutinizer.yml`
- After adding a handler, you MUST uncomment or add its entry in `getHooks()` to activate it

## Instructions

1. **Identify the event name and subject type.** Determine the Symfony event string (e.g. `'system.settings'`, `'ui.menu'`) and what `getSubject()` returns (e.g. `\MyAdmin\Settings`, `\MyAdmin\Plugins\Loader`, a menu array). Verify the event string matches a hook registered elsewhere in MyAdmin before proceeding.

2. **Add the PHPDoc block and method signature to `src/Plugin.php`** after the last existing handler, inside the `Plugin` class:

```php
	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getMyFeature(GenericEvent $event)
	{
		/**
		 * @var \MyAdmin\SomeType $subject
		 **/
		$subject = $event->getSubject();
	}
```

   Replace `getMyFeature` with a camelCase name matching the event purpose. Verify the method does not already exist before adding.

3. **Add ACL guard if the handler renders UI or mutates admin data.** Mirror the pattern from `getMenu()` in `src/Plugin.php`:

```php
		if ($GLOBALS['tf']->ima == 'admin') {
			function_requirements('has_acl');
			if (has_acl('client_billing')) {
				// admin-only logic here
			}
		}
```

   Skip this block for handlers like `getRequirements` or `getSettings` that run unconditionally.

4. **Register the handler in `getHooks()`.** Add the event→callable entry using `__CLASS__`:

```php
public static function getHooks()
{
	return [
		'system.settings' => [__CLASS__, 'getSettings'],
		'my.event.name'   => [__CLASS__, 'getMyFeature'],
	];
}
```

   Verify no duplicate event key exists in the returned array.

5. **Add a test in `tests/PluginTest.php`** using `ReflectionClass` to assert the method signature:

```php
public function testGetMyFeatureMethodSignature(): void
{
	$method = $this->reflection->getMethod('getMyFeature');
	$this->assertTrue($method->isStatic());
	$this->assertTrue($method->isPublic());

	$params = $method->getParameters();
	$this->assertCount(1, $params);
	$this->assertSame('event', $params[0]->getName());

	$paramType = $params[0]->getType();
	$this->assertNotNull($paramType);
	$this->assertSame(GenericEvent::class, $paramType->getName());
}
```

   Also update `testClassPublicMethods()` to include `'getMyFeature'` in `$expectedMethods`.

6. **Run tests** to verify nothing is broken. Verify `src/Plugin.php` and `tests/PluginTest.php` pass syntax and the full test suite is green:

```bash
php -l src/Plugin.php && php -l tests/PluginTest.php
```

   All tests must pass before considering the task complete.

## Examples

**User says:** "Add an event handler for the `module.load` event that registers a service with the loader."

**Actions taken:**
1. Add to `src/Plugin.php`:
```php
	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getLoad(GenericEvent $event)
	{
		/**
		 * @var \MyAdmin\Plugins\Loader $loader
		 **/
		$loader = $event->getSubject();
		$loader->add_requirement('class.Kayako', '/../vendor/detain/myadmin-kayako-chat/src/Kayako.php');
	}
```
2. Update `getHooks()` return array to add `'module.load' => [__CLASS__, 'getLoad']`.
3. Add `testGetLoadMethodSignature()` to `tests/PluginTest.php` and add `'getLoad'` to `$expectedMethods` in `testClassPublicMethods()`.
4. Run syntax check and full test suite — all tests pass.

**Result:** Handler is registered, activated, and covered by a signature test.

## Common Issues

- **`Call to undefined function has_acl()`** — missing `function_requirements('has_acl');` call before `has_acl()`. Add it immediately before the `if (has_acl(...))` line.
- **`testClassPublicMethods` fails with "Missing public method: getMyFeature"** — you added the method but forgot to add `'getMyFeature'` to `$expectedMethods` in that test.
- **Hook never fires** — method was added but the entry in `getHooks()` is still commented out. Uncomment or add the entry to the returned array.
- **`testGetHooksReturnsEmptyArray` fails after enabling a hook** — this test asserts `assertEmpty($hooks)`. Update it to `assertNotEmpty($hooks)` or replace with a count assertion once hooks are active.
- **Indentation lint errors in `.scrutinizer.yml` check** — file was saved with spaces. Convert to tabs: `unexpand --first-only -t 4 src/Plugin.php > /tmp/p.php && mv /tmp/p.php src/Plugin.php`.
