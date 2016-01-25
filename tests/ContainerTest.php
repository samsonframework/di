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
        //$instance = $container->get('\samsonframework\di\tests\TestModuleClass');

        file_put_contents(__DIR__.'/ContainerLogic.php', '<?php '.$container->generateLogicFunction());
    }

    public function testHas()
    {

    }
}
