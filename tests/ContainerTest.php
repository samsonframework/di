<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 12.01.16
 * Time: 14:33
 */
namespace samsonframework\di\tests;

use samsonframework\di\ClosureContainer;
use samsonframework\di\Container;
use samsonphp\generator\Generator;

require_once 'TestModuleClass.php';
require_once 'TestServiceClass.php';
require_once 'OtherTestClass.php';
require_once 'OtherSecondTestClass.php';
require_once 'OtherThirdTestClass.php';

class ContainerTest extends \PHPUnit_Framework_TestCase
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

        $closureContainer = new ClosureContainer(new Generator());
        $closureContainer->set(function() {
            return new \samsonframework\di\tests\OtherTestClass(
                new \samsonframework\di\tests\OtherThirdTestClass(
                    new \samsonframework\di\tests\OtherSecondTestClass()
                ),
                array('1'),
                '1'
            );
        }, 'callbackTest');

        $this->container->delegate($closureContainer);

        // Create logic and import it
        $logic = $this->container->generateFunction();
        $path = __DIR__.'/ContainerLogic.php';
        file_put_contents($path, '<?php '.$logic);
        require_once $path;
    }

    public function testGet()
    {
        /** @var \samsonframework\di\tests\TestModuleClass $instance */
        $instance = $this->container->get('\samsonframework\di\tests\TestModuleClass');

        $this->assertTrue($instance instanceof \samsonframework\di\tests\TestModuleClass);
        $this->assertTrue($instance->dependency1 instanceof \samsonframework\di\tests\OtherTestClass);
        $this->assertTrue($instance->dependency2 instanceof \samsonframework\di\tests\OtherSecondTestClass);
        $this->assertTrue($instance->dependency1->dependency1 instanceof \samsonframework\di\tests\OtherThirdTestClass);

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
    }
}
