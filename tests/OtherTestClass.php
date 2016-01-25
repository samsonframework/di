<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 18:23
 */
namespace samsonframework\di\tests;

class OtherTestClass
{
    public $dependency1;
    public $array;
    public $string;
    public $optional;

    public function __construct(
        OtherThirdTestClass $dependency1,
        array $arrayParam,
        $stringParam,
        $optionalParam = ''
    ) {
        $this->dependency1 = $dependency1;
        $this->array = $arrayParam;
        $this->string = $stringParam;
        $this->optional = $optionalParam;
    }
}
