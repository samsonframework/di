<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 08.08.16 at 14:20
 */
namespace samsonframework\di\tests;

use samsonframework\di\ClosureContainer;
use samsonframework\di\Container;
use samsonframework\di\exception\ClassNotFoundException;
use samsonphp\generator\Generator;

class ClosureContainerTest extends TestCase
{
    /** @var ClosureContainer */
    protected $closureContainer;

    /** @var string Closure container callback */
    protected $closureCallable = 'testCallback';

    public function setUp()
    {
        $this->container = new Container(new Generator());

        $this->setContainerDependencies();

        $this->closureContainer = new ClosureContainer(new Generator());

        $this->closureContainer->set(function() {
            if (true) {
                /*just for test*/
            }
            return new \samsonframework\di\tests\OtherTestClass(
                new \samsonframework\di\tests\OtherThirdTestClass(
                    new \samsonframework\di\tests\OtherSecondTestClass()
                ),
                array('1'),
                '1'
            );
        }, [], $this->closureCallable);

        $this->container->delegate($this->closureContainer);
    }

    public function testDelegateHas()
    {
        $this->createLogic('closure');
        static::assertTrue($this->container->has($this->closureCallable));
        static::assertTrue($this->container->has($this->testServiceAlias));
        static::assertTrue($this->container->has(OtherThirdTestClass::class));
        static::assertFalse($this->container->has('IDoNotExistsClass'));
    }

    public function testDelegateGet()
    {
        $this->createLogic('closure2');
        static::assertInstanceOf(OtherTestClass::class, $this->container->get($this->closureCallable));
    }

    public function testDelegateGetClassNotFoundException()
    {
        $this->expectException(ClassNotFoundException::class);
        $this->createLogic('closure3');
        $this->container->get('IDoNotExistsClass');
    }
}
