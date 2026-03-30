---
name: phpunit-reflection-test
description: Scaffolds a PHPUnit test in `tests/` using `ReflectionClass` to verify method signatures, static modifiers, and property visibility. Use when user says 'add test', 'write tests for', or adds a new method to `src/Plugin.php`. Follows patterns in `tests/PluginTest.php`. Do NOT use for integration tests requiring live DB or HTTP.
---
# phpunit-reflection-test

## Critical

- Test file lives in `tests/`, namespace `Detain\MyAdminKayakoChat\Tests`, extends `PHPUnit\Framework\TestCase`.
- Use tabs for indentation (`.scrutinizer.yml` enforces this — spaces will fail style checks).
- `ReflectionClass` must be instantiated in `setUp()` as `private ReflectionClass $reflection` and reused across tests — never re-instantiate per test.
- Never test against live DB, HTTP, or filesystem — this plugin defers all that to the parent MyAdmin system.
- Run the full test suite to verify before finishing. All tests must pass.

## Instructions

1. **Identify what to test.** Read `src/Plugin.php`. Note every `public static` method and `public static` property. Verify the class namespace is `Detain\MyAdminKayakoChat`.

2. **Create or update `tests/PluginTest.php`.** File header boilerplate:
```php
<?php

namespace Detain\MyAdminKayakoChat\Tests;

use Detain\MyAdminKayakoChat\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

class PluginTest extends TestCase
{
	/**
	 * @var ReflectionClass<Plugin>
	 */
	private ReflectionClass $reflection;

	protected function setUp(): void
	{
		$this->reflection = new ReflectionClass(Plugin::class);
	}
```

3. **Add instantiation test** (always first):
```php
public function testCanBeInstantiated(): void
{
	$plugin = new Plugin();
	$this->assertInstanceOf(Plugin::class, $plugin);
}
```

4. **Add static property value tests** — one test per property, assert exact value:
```php
public function testNamePropertyValue(): void
{
	$this->assertSame('Kayako Plugin', Plugin::$name);
}
```
 Verify values by reading `src/Plugin.php` directly before writing each assertion.

5. **Add a combined visibility test** for all static properties:
```php
public function testStaticPropertiesArePublic(): void
{
	$properties = ['name', 'description', 'help', 'type'];
	foreach ($properties as $propertyName) {
		$property = $this->reflection->getProperty($propertyName);
		$this->assertTrue($property->isPublic(), "Property \${$propertyName} should be public");
		$this->assertTrue($property->isStatic(), "Property \${$propertyName} should be static");
	}
}
```

6. **Add method signature tests** for each `public static function` that accepts a `GenericEvent`:
```php
public function testGetMenuMethodSignature(): void
{
	$method = $this->reflection->getMethod('getMenu');
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
 Repeat for `getRequirements`, `getSettings`, and any new methods added.

7. **Add `getRequirements` behavior test** using an anonymous class mock (no Mockery/Prophecy):
```php
public function testGetRequirementsCallsAddRequirement(): void
{
	$calls = [];
	$loader = new class($calls) {
		/** @var array<int, array{string, string}> */
		private array $callsRef;
		public function __construct(array &$calls) { $this->callsRef = &$calls; }
		public function add_requirement(string $name, string $path): void {
			$this->callsRef[] = [$name, $path];
		}
	};
	$event = new GenericEvent($loader);
	Plugin::getRequirements($event);
	$this->assertCount(4, $calls); // update count to match actual calls in src/Plugin.php
	$this->assertSame('class.Kayako', $calls[0][0]);
}
```
 Verify the exact requirement names and count by reading `src/Plugin.php:getRequirements` before writing assertions.

8. **Run tests.** From the project root, verify all tests in `tests/PluginTest.php` pass:

```bash
php -l src/Plugin.php && php -l tests/PluginTest.php
```

 All tests must be green before done.

## Examples

**User says:** "Add tests for the new `getStatus` static method I added to `src/Plugin.php`."

**Actions:**
1. Read `src/Plugin.php` — confirm `getStatus(GenericEvent $event)` exists, is `public static`.
2. Add to `tests/PluginTest.php`:
```php
public function testGetStatusMethodSignature(): void
{
	$method = $this->reflection->getMethod('getStatus');
	$this->assertTrue($method->isStatic());
	$this->assertTrue($method->isPublic());
	$params = $method->getParameters();
	$this->assertCount(1, $params);
	$this->assertSame('event', $params[0]->getName());
	$paramType = $params[0]->getType();
	$this->assertNotNull($paramType);
	$this->assertSame(GenericEvent::class, $paramType->getName());
}

public function testGetStatusAcceptsGenericEvent(): void
{
	$event = new GenericEvent(new \stdClass());
	Plugin::getStatus($event);
	$this->assertTrue(true);
}
```
3. Run the test suite — all green.

## Common Issues

- **`Call to undefined method ... getType()->getName()`**: PHP < 7.4 returns `ReflectionNamedType` only in 7.4+. Ensure `composer.json` shows `"php": ">=5.0.0"` but the test environment actually runs PHP 7.4+. Run `php --version` to verify.
- **`Property ... does not exist`**: The property name in the assertion doesn't match `src/Plugin.php`. Re-read the file — property names are case-sensitive (`$name` not `$Name`).
- **`Failed asserting that 4 is equal to N`** in `testGetRequirementsCallsAddRequirement`: The count of `add_requirement` calls in `Plugin::getRequirements` changed. Re-read `src/Plugin.php` and update `assertCount(N, $calls)` to match actual calls.
- **`Tabs/spaces` style warning from scrutinizer**: Editor converted tabs to spaces. The project uses tabs. Run `unexpand --first-only tests/PluginTest.php` to convert, or configure your editor to indent with tabs for this project.
- **PHPUnit binary not found**: `require-dev` omitted from `composer.json`. Fix: add `"phpunit/phpunit": "^9.6"` under `require-dev` and re-run `composer install`.
