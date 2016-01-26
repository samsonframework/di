<?php 
function diContainer($aliasOrClassName)
{
    static $services;
    
    if ($aliasOrClassName === '\samsonframework\di\tests\OtherTestClass') {
        return new \samsonframework\di\tests\OtherTestClass(
            new \samsonframework\di\tests\OtherThirdTestClass(
                new \samsonframework\di\tests\OtherSecondTestClass()
            ),
            array(
                '0' => '0',
                '1' => '1',
                '2' => '2',
                '3' => '3',
            ),
            'I am string2'
        );
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\OtherThirdTestClass') {
        return new \samsonframework\di\tests\OtherThirdTestClass(
            new \samsonframework\di\tests\OtherSecondTestClass()
        );
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\TestModuleClass') {
        return new \samsonframework\di\tests\TestModuleClass(
            new \samsonframework\di\tests\OtherTestClass(
                new \samsonframework\di\tests\OtherThirdTestClass(
                    new \samsonframework\di\tests\OtherSecondTestClass()
                ),
                array(
                    '0' => '0',
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                ),
                'I am string2'
            ),
            new \samsonframework\di\tests\OtherSecondTestClass(),
            array(
                '0' => '1',
                '1' => '2',
                '2' => '3',
            ),
            'I am string'
        );
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\TestServiceClass') {
        return isset($services['\samsonframework\di\tests\TestServiceClass'])
        ? $services['\samsonframework\di\tests\TestServiceClass']
        : $services['\samsonframework\di\tests\TestServiceClass'] = new \samsonframework\di\tests\TestServiceClass(
            new \samsonframework\di\tests\TestModuleClass(
                new \samsonframework\di\tests\OtherTestClass(
                    new \samsonframework\di\tests\OtherThirdTestClass(
                        new \samsonframework\di\tests\OtherSecondTestClass()
                    ),
                    array(
                        '0' => '0',
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                    ),
                    'I am string2'
                ),
                new \samsonframework\di\tests\OtherSecondTestClass(),
                array(
                    '0' => '1',
                    '1' => '2',
                    '2' => '3',
                ),
                'I am string'
            ),
            array(
                '0' => '1',
                '1' => '2',
                '2' => '3',
            ),
            'I am string'
        );
    } elseif ($aliasOrClassName === 'callbackTest') {
        return new \samsonframework\di\tests\OtherTestClass(
            new \samsonframework\di\tests\OtherThirdTestClass(
                new \samsonframework\di\tests\OtherSecondTestClass()
            ),
            array('1'),
            '1'
        );
    }
}
