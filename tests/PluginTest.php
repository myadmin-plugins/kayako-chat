<?php

namespace Detain\MyAdminKayakoChat\Tests;

use Detain\MyAdminKayakoChat\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class PluginTest
 *
 * Unit tests for the Kayako Chat Plugin class.
 *
 * @package Detain\MyAdminKayakoChat\Tests
 */
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

    /**
     * Tests that the Plugin class can be instantiated without errors.
     */
    public function testCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    /**
     * Tests that the $name static property is set to the expected value.
     */
    public function testNamePropertyValue(): void
    {
        $this->assertSame('Kayako Plugin', Plugin::$name);
    }

    /**
     * Tests that the $description static property is set to the expected value.
     */
    public function testDescriptionPropertyValue(): void
    {
        $this->assertSame('Allows handling of Kayako Live Chat.', Plugin::$description);
    }

    /**
     * Tests that the $help static property is an empty string.
     */
    public function testHelpPropertyIsEmptyString(): void
    {
        $this->assertSame('', Plugin::$help);
    }

    /**
     * Tests that the $type static property is set to 'plugin'.
     */
    public function testTypePropertyValue(): void
    {
        $this->assertSame('plugin', Plugin::$type);
    }

    /**
     * Tests that all expected static properties exist on the class.
     */
    public function testStaticPropertiesExist(): void
    {
        $this->assertTrue($this->reflection->hasProperty('name'));
        $this->assertTrue($this->reflection->hasProperty('description'));
        $this->assertTrue($this->reflection->hasProperty('help'));
        $this->assertTrue($this->reflection->hasProperty('type'));
    }

    /**
     * Tests that all static properties are declared as public.
     */
    public function testStaticPropertiesArePublic(): void
    {
        $properties = ['name', 'description', 'help', 'type'];
        foreach ($properties as $propertyName) {
            $property = $this->reflection->getProperty($propertyName);
            $this->assertTrue($property->isPublic(), "Property \${$propertyName} should be public");
            $this->assertTrue($property->isStatic(), "Property \${$propertyName} should be static");
        }
    }

    /**
     * Tests that all static properties hold string values.
     */
    public function testStaticPropertiesAreStrings(): void
    {
        $this->assertIsString(Plugin::$name);
        $this->assertIsString(Plugin::$description);
        $this->assertIsString(Plugin::$help);
        $this->assertIsString(Plugin::$type);
    }

    /**
     * Tests that getHooks returns an array.
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Tests that getHooks currently returns an empty array (all hooks are commented out).
     */
    public function testGetHooksReturnsEmptyArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertEmpty($hooks);
    }

    /**
     * Tests that the getHooks method is declared as static.
     */
    public function testGetHooksIsStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isStatic());
    }

    /**
     * Tests that getHooks is a public method.
     */
    public function testGetHooksIsPublic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Tests that the getMenu method exists and has the correct signature.
     */
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

    /**
     * Tests that the getRequirements method exists and has the correct signature.
     */
    public function testGetRequirementsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
    }

    /**
     * Tests that the getSettings method exists and has the correct signature.
     */
    public function testGetSettingsMethodSignature(): void
    {
        $method = $this->reflection->getMethod('getSettings');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertSame('event', $params[0]->getName());

        $paramType = $params[0]->getType();
        $this->assertNotNull($paramType);
        $this->assertSame(GenericEvent::class, $paramType->getName());
    }

    /**
     * Tests that the constructor takes no parameters.
     */
    public function testConstructorHasNoParameters(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    /**
     * Tests that the Plugin class is in the expected namespace.
     */
    public function testClassNamespace(): void
    {
        $this->assertSame('Detain\\MyAdminKayakoChat', $this->reflection->getNamespaceName());
    }

    /**
     * Tests that the Plugin class is not abstract.
     */
    public function testClassIsNotAbstract(): void
    {
        $this->assertFalse($this->reflection->isAbstract());
    }

    /**
     * Tests that the Plugin class is not an interface.
     */
    public function testClassIsNotInterface(): void
    {
        $this->assertFalse($this->reflection->isInterface());
    }

    /**
     * Tests that the class has exactly the expected public methods.
     */
    public function testClassPublicMethods(): void
    {
        $expectedMethods = ['__construct', 'getHooks', 'getMenu', 'getRequirements', 'getSettings'];
        $publicMethods = array_map(
            fn(\ReflectionMethod $m) => $m->getName(),
            $this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC)
        );

        foreach ($expectedMethods as $method) {
            $this->assertContains($method, $publicMethods, "Missing public method: {$method}");
        }
    }

    /**
     * Tests that getSettings can be called with a GenericEvent without error.
     */
    public function testGetSettingsAcceptsGenericEvent(): void
    {
        $subject = new \stdClass();
        $event = new GenericEvent($subject);

        // Should not throw any exception
        Plugin::getSettings($event);
        $this->assertTrue(true);
    }

    /**
     * Tests that getRequirements calls add_requirement on the event subject.
     */
    public function testGetRequirementsCallsAddRequirement(): void
    {
        $calls = [];
        $loader = new class($calls) {
            /** @var array<int, array{string, string}> */
            private array $callsRef;

            /**
             * @param array<int, array{string, string}> $calls
             */
            public function __construct(array &$calls)
            {
                $this->callsRef = &$calls;
            }

            /**
             * @param string $name
             * @param string $path
             */
            public function add_requirement(string $name, string $path): void
            {
                $this->callsRef[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $this->assertCount(4, $calls);
        $this->assertSame('class.Kayako', $calls[0][0]);
        $this->assertSame('deactivate_kcare', $calls[1][0]);
        $this->assertSame('deactivate_abuse', $calls[2][0]);
        $this->assertSame('get_abuse_licenses', $calls[3][0]);
    }

    /**
     * Tests that getRequirements registers paths containing the expected package directory.
     */
    public function testGetRequirementsRegistersCorrectPaths(): void
    {
        $calls = [];
        $loader = new class($calls) {
            /** @var array<int, array{string, string}> */
            private array $callsRef;

            /**
             * @param array<int, array{string, string}> $calls
             */
            public function __construct(array &$calls)
            {
                $this->callsRef = &$calls;
            }

            /**
             * @param string $name
             * @param string $path
             */
            public function add_requirement(string $name, string $path): void
            {
                $this->callsRef[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        foreach ($calls as $call) {
            $this->assertStringContainsString('myadmin-kayako-chat', $call[1]);
        }
    }

    /**
     * Tests that hook values, if present, follow the expected callable format.
     */
    public function testHookValuesFormatIfNotEmpty(): void
    {
        $hooks = Plugin::getHooks();
        foreach ($hooks as $eventName => $callable) {
            $this->assertIsString($eventName, 'Hook event name should be a string');
            $this->assertIsArray($callable, 'Hook callable should be an array');
            $this->assertCount(2, $callable, 'Hook callable should have class and method');
        }
        // Currently empty, so this test passes trivially confirming the format contract
        $this->assertIsArray($hooks);
    }

    /**
     * Tests that the class has exactly four static properties.
     */
    public function testStaticPropertyCount(): void
    {
        $staticProperties = array_filter(
            $this->reflection->getProperties(\ReflectionProperty::IS_STATIC),
            fn(\ReflectionProperty $p) => $p->getDeclaringClass()->getName() === Plugin::class
        );
        $this->assertCount(4, $staticProperties);
    }
}
