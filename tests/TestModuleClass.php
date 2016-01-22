<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 18:23
 */
namespace samsonframework\di\tests;

class TestModuleClass
{
    public function __construct(OtherTestClass $dependency1, OtherSecondTestClass $dependency2)
    {

    }
}
