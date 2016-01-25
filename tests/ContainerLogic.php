<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 25.01.16 at 08:47
 */
function di($aliasOrClassName)
{
    static $services;

    if ($aliasOrClassName === 'TestModuleClass') {
        return new \samsonframework\di\tests\TestModuleClass(
            new \samsonframework\di\tests\OtherTestClass(
                isset($services['\samsonframework\di\tests\OtherThirdTestClass'])
                    ? $services['\samsonframework\di\tests\OtherThirdTestClass']
                    : $services['\samsonframework\di\tests\OtherThirdTestClass'] = new \samsonframework\di\tests\OtherThirdTestClass(),
                array(),
                'sdfsdf'
            ),
            new \samsonframework\di\tests\OtherSecondTestClass(),
            array(),
            'sdfds'
        );
    }
}
