<?php 
function container3($aliasOrClassName)
{
    static $services;
    
    if ($aliasOrClassName === 'samsonframework\di\tests\TestServiceClass' || $aliasOrClassName === 'test_service') {
        return isset($services['test_service'])
        ? $services['test_service']
        : $services['test_service'] = new samsonframework\di\tests\TestServiceClass(
            container3('samsonframework\di\tests\TestModuleClass'),
            array(
                '0' => '1',
                '1' => '2',
                '2' => '3',
            ),
            'I am string'
        );
    } elseif ($aliasOrClassName === 'samsonframework\di\tests\TestModuleClass' || $aliasOrClassName === 'samsonframework\di\tests\TestModuleClass') {
        return new samsonframework\di\tests\TestModuleClass(
            container3('samsonframework\di\tests\OtherTestClass'),
            container3('samsonframework\di\tests\OtherSecondTestClass'),
            array(
                '0' => '1',
                '1' => '2',
                '2' => '3',
            ),
            'I am string'
        );
    } elseif ($aliasOrClassName === 'samsonframework\di\tests\OtherTestClass' || $aliasOrClassName === 'samsonframework\di\tests\OtherTestClass') {
        return new samsonframework\di\tests\OtherTestClass(
            container3('samsonframework\di\tests\OtherThirdTestClass'),
            array(
                '0' => '2',
                '1' => '1',
                '2' => '2',
                '3' => '3',
            ),
            'I am string2'
        );
    } elseif ($aliasOrClassName === 'samsonframework\di\tests\OtherThirdTestClass' || $aliasOrClassName === 'samsonframework\di\tests\OtherThirdTestClass') {
        return new samsonframework\di\tests\OtherThirdTestClass(
            container3('samsonframework\di\tests\OtherSecondTestClass')
        );
    } elseif ($aliasOrClassName === 'samsonframework\di\tests\OtherSecondTestClass' || $aliasOrClassName === 'samsonframework\di\tests\OtherSecondTestClass') {
        return new samsonframework\di\tests\OtherSecondTestClass();
    }
}
