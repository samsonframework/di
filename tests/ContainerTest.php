<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 12.01.16
 * Time: 14:33
 */
namespace samsonframework\di\tests;

use samsonframework\di\Container;
use samsonframework\di\exception\ClassNotFoundException;
use samsonframework\di\exception\ContainerException;
use samsonphp\generator\Generator;

class ContainerTest extends TestCase
{
    public function setUp()
    {
        $this->container = new Container(new Generator());
        $this->setContainerDependencies();
    }

    public function testLogicFailed()
    {
        $this->expectException(ContainerException::class);
        $this->container->get('doesNotMatter');
    }

    public function testSetWithoutAlias()
    {
        $this->container->set(TestModuleClass::class, ['dependency1' => OtherTestClass::class]);

        static::assertArrayHasKey(TestModuleClass::class, $this->getProperty('dependencies', $this->container));
        static::assertArrayHasKey('dependency1', $this->getProperty('dependencies', $this->container)[TestModuleClass::class]);
    }

    public function testSetWithAlias()
    {
        $alias = 'testAlias';
        $this->container->set(TestModuleClass::class, ['dependency1' => OtherTestClass::class], $alias);

        static::assertArrayHasKey(TestModuleClass::class, $this->getProperty('aliases', $this->container));
        static::assertEquals($alias, $this->getProperty('aliases', $this->container)[TestModuleClass::class]);
    }

    public function testHas()
    {
        static::assertTrue($this->container->has(TestModuleClass::class));
        static::assertTrue($this->container->has($this->testServiceAlias));
        static::assertTrue($this->container->has(OtherThirdTestClass::class));
        static::assertFalse($this->container->has('IDoNotExists'));
    }

//    public function testNestedClassContainer()
//    {
//        /** @var TestModuleClass $instance */
//        $instance = $this->container->get(TestModuleClass::class);
//
//        static::assertTrue($instance instanceof TestModuleClass);
//        static::assertTrue($instance->dependency1 instanceof OtherTestClass);
//        static::assertTrue($instance->dependency2 instanceof OtherSecondTestClass);
//        static::assertTrue($instance->dependency1->dependency1 instanceof OtherThirdTestClass);
//        static::assertEquals([1,2,3], $instance->array);
//        static::assertEquals('I am string', $instance->string);
//        static::assertEquals([2,1,2,3], $instance->dependency1->array);
//        static::assertEquals('I am string2', $instance->dependency1->string);
//    }
//
//    public function testServiceContainer()
//    {
//        /** @var TestServiceClass $instance */
//        $instance = $this->container->get(TestServiceClass::class);
//
//        static::assertInstanceOf(TestServiceClass::class, $instance);
//        static::assertEquals([1,2,3], $instance->array);
//        static::assertEquals('I am string', $instance->string);
//
//        /** @var TestServiceClass $service */
//        $service = $this->container->get(TestServiceClass::class);
//
//        static::assertSame($service, $instance);
//        static::assertSame($service->dependency1, $instance->dependency1);
//    }
//
//    public function testGetClassNotFoundException()
//    {
//        $this->expectException(ClassNotFoundException::class);
//        $this->container->get('NotExistingClass');
//    }
//
//    public function testHas()
//    {
//        static::assertTrue($this->container->has(TestModuleClass::class));
//        static::assertTrue($this->container->has($this->testServiceAlias));
//        static::assertTrue($this->container->has(OtherThirdTestClass::class));
//        static::assertFalse($this->container->has('IDoNotExists'));
//    }
}
