<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 12.01.16
 * Time: 14:33
 */
namespace samsonframework\di\tests;

use phpDocumentor\Reflection\DocBlock\Tags\Param;
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

    public function testHasWithDelegate()
    {
        $delegate = new Container();
        $delegate->set(TestCase::class);
        $this->container->delegate($delegate);

        static::assertTrue($this->container->has(TestCase::class));
        static::assertFalse($this->container->has('IDoNotExists'));
    }

    public function dependencyResolver($alias)
    {
        if ($alias === TestModuleClass::class) {
            return new TestModuleClass(
                new OtherTestClass(
                    new OtherThirdTestClass(
                        new OtherSecondTestClass()
                    ),
                    [2, 1, 2, 3],
                    'I am string2'
                ),
                new OtherSecondTestClass(),
                [1, 2, 3],
                'I am string'
            );
        } elseif ($alias === 'testService') {
            $service = new OtherSecondTestClass();
            $this->setProperty('serviceInstances', $this->container, ['testService' => $service]);
            return $service;
        }
    }

    public function delegateDependencyResolver($alias)
    {
        if ($alias === TestCase::class) {
            return new TestCase();
        }
    }

    public function testGet()
    {
        $this->setProperty('logicCallable', $this->container, [$this, 'dependencyResolver']);

        /** @var TestModuleClass $instance */
        $instance = $this->container->get(TestModuleClass::class);

        static::assertTrue($instance instanceof TestModuleClass);
        static::assertTrue($instance->dependency1 instanceof OtherTestClass);
        static::assertTrue($instance->dependency2 instanceof OtherSecondTestClass);
        static::assertTrue($instance->dependency1->dependency1 instanceof OtherThirdTestClass);
        static::assertEquals([1,2,3], $instance->array);
        static::assertEquals('I am string', $instance->string);
        static::assertEquals([2,1,2,3], $instance->dependency1->array);
        static::assertEquals('I am string2', $instance->dependency1->string);
    }

    public function testGetWithDelegateExistingDependency()
    {
        $delegate = new Container();
        $delegate->set(TestCase::class);
        $this->container->delegate($delegate);

        $this->setProperty('logicCallable', $this->container, [$this, 'dependencyResolver']);
        $this->setProperty('logicCallable', $delegate, [$this, 'delegateDependencyResolver']);

        static::assertInstanceOf(TestCase::class, $this->container->get(TestCase::class));
        //static::assertFalse($this->container->has('IDoNotExists'));
    }

    public function testGetWithDelegateNotExistingDependency()
    {
        $this->expectException(ClassNotFoundException::class);
        $delegate = new Container();
        $delegate->set(TestCase::class);
        $this->container->delegate($delegate);

        $this->setProperty('logicCallable', $this->container, [$this, 'dependencyResolver']);
        $this->container->get(TestCase::class);
    }

    public function testGetServices()
    {
        $serviceName = 'testService';
        $this->container->service(OtherSecondTestClass::class, [], $serviceName);
        $this->setProperty('logicCallable', $this->container, [$this, 'dependencyResolver']);

        $serviceInstance = $this->container->get($serviceName);
        static::assertInstanceOf(OtherSecondTestClass::class, $serviceInstance);
        static::assertArrayHasKey($serviceName, $this->container->getServices());
    }
}
