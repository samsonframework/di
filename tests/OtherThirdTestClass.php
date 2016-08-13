<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 18:23
 */
namespace samsonframework\di\tests;

class OtherThirdTestClass
{
    /** @var OtherSecondTestClass  */
    public $dependency1;

    public function __construct(OtherSecondTestClass $dependency1)
    {
        $this->dependency1 = $dependency1;
    }
}
