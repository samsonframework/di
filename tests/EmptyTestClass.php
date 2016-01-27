<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 18:23
 */
namespace samsonframework\di\tests;

class EmptyTestClass implements TestInterface
{
    protected $failedParam;

    public function __construct($failedParam)
    {
        $this->failedParam = $failedParam;
    }
}
