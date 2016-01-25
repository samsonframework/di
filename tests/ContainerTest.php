<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 12.01.16
 * Time: 14:33
 */
namespace samsonframework\di\tests;

use samsonframework\di\Container;
use samsonphp\generator\Generator;

require 'TestModuleClass.php';
require 'TestServiceClass.php';
require 'OtherTestClass.php';
require 'OtherSecondTestClass.php';
require 'OtherThirdTestClass.php';

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Container */
    protected $container;

    public function setUp()
    {
        $this->container = new Container(new Generator());
        $this->container->set(
            '\samsonframework\di\tests\OtherTestClass',
            'otherTestModule',
            array('arrayParam' => array(0,1,2,3), 'stringParam' => 'I am string2')
        );

        $this->container->set(
            '\samsonframework\di\tests\TestModuleClass',
            'testModule',
            array('arrayParam' => array(1,2,3), 'stringParam' => 'I am string')
        );

        $this->container->service(
            '\samsonframework\di\tests\TestServiceClass',
            'testService',
            array('arrayParam' => array(1,2,3), 'stringParam' => 'I am string')
        );

        $this->container->callback(function() {
            return new \samsonframework\di\tests\OtherTestClass(
                new \samsonframework\di\tests\OtherThirdTestClass(
                    new \samsonframework\di\tests\OtherSecondTestClass()
                ),
                array('1'),
                '1'
            );
        }, 'callbackTest');
    }

    public function testGet()
    {
        // Create logic
        $logic = $this->container->generateLogicFunction();
        eval($logic);
        file_put_contents(__DIR__.'/ContainerLogic.php', '<?php '.$logic);

        /** @var \samsonframework\di\tests\TestModuleClass $instance */
        $instance = $this->container->get('\samsonframework\di\tests\TestModuleClass');

        $this->assertTrue($instance instanceof \samsonframework\di\tests\TestModuleClass);
        $this->assertTrue($instance->dependency1 instanceof \samsonframework\di\tests\OtherTestClass);
        $this->assertTrue($instance->dependency2 instanceof \samsonframework\di\tests\OtherSecondTestClass);
        $this->assertTrue($instance->dependency1->dependency1 instanceof \samsonframework\di\tests\OtherThirdTestClass);
    }

    public function testService()
    {
        /** @var \samsonframework\di\tests\TestServiceClass $service */
        $service = $this->container->get('\samsonframework\di\tests\TestServiceClass');
        /** @var \samsonframework\di\tests\TestServiceClass $service2 */
        $service2 = $this->container->get('\samsonframework\di\tests\TestServiceClass');

        $this->assertTrue($service instanceof \samsonframework\di\tests\TestServiceClass);
        $this->assertTrue($service === $service2);
        $this->assertTrue($service->dependency1 === $service2->dependency1);
    }

    public function testHas()
    {
        $this->assertTrue($this->container->has('\samsonframework\di\tests\TestModuleClass'));
        $this->assertTrue($this->container->has('testModule'));
        $this->assertTrue($this->container->has('testService'));
    }

    public function testClosure()
    {
        $this->assertTrue($this->container->get('callbackTest') instanceof \samsonframework\di\tests\OtherTestClass);
    }
}
