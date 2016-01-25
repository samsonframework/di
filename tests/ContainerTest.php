<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 12.01.16
 * Time: 14:33
 */
namespace samsonframework\di\tests;

use samsonframework\di\Container;

require 'TestModuleClass.php';

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $container = new Container();
        $container->set('\samsonframework\di\tests\TestModuleClass', 'testModule');
        //$instance = $container->get('\samsonframework\di\tests\TestModuleClass');
    }

    public function testHas()
    {

    }
}
