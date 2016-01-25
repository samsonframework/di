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
require 'OtherTestClass.php';
require 'OtherSecondTestClass.php';
require 'OtherThirdTestClass.php';

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $container = new Container(new Generator());
        $container->set(
            '\samsonframework\di\tests\OtherTestClass',
            'otherTestModule',
            array('arrayParam' => array(0,1,2,3), 'stringParam' => 'I am string2')
        );

        $container->set(
            '\samsonframework\di\tests\TestModuleClass',
            'testModule',
            array('arrayParam' => array(1,2,3), 'stringParam' => 'I am string')
        );

        // Create logic
        $logic = $container->generateLogicFunction();
        eval($logic);
        file_put_contents(__DIR__.'/ContainerLogic.php', '<?php '.$logic);

        /** @var \samsonframework\di\tests\TestModuleClass $instance */
        $instance = $container->get('\samsonframework\di\tests\TestModuleClass');

        $this->assertEquals(true, $instance instanceof \samsonframework\di\tests\TestModuleClass);
        $this->assertEquals(true, $instance->dependency1 instanceof \samsonframework\di\tests\OtherTestClass);
        $this->assertEquals(true, $instance->dependency2 instanceof \samsonframework\di\tests\OtherSecondTestClass);
        $this->assertEquals(true, $instance->dependency1->dependency1 instanceof \samsonframework\di\tests\OtherThirdTestClass);

    }

    public function testHas()
    {

    }
}
