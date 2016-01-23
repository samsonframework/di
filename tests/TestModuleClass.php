<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 18:23
 */
namespace samsonframework\di\tests;

class TestModuleClass
{
    public $dependency1;

    public $dependency2;

    public function __construct(OtherTestClass $dependency1, OtherSecondTestClass $dependency2)
    {
        $this->dependency1 = $dependency1;
        $this->dependency2 = $dependency2;
    }
}
