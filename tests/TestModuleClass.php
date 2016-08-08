<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 18:23
 */
namespace samsonframework\di\tests;

class TestModuleClass
{
    /** @var OtherTestClass  */
    public $dependency1;
    /** @var OtherSecondTestClass  */
    public $dependency2;
    
    public $array;
    public $string;
    public $optional;

    public function __construct(
        OtherTestClass $dependency1,
        OtherSecondTestClass $dependency2,
        array $arrayParam,
        $stringParam,
        $optionalParam = ''
    ) {
        $this->dependency1 = $dependency1;
        $this->dependency2 = $dependency2;
        $this->array = $arrayParam;
        $this->string = $stringParam;
        $this->optional = $optionalParam;
    }
}
