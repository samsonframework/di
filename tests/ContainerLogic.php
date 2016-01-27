<?php 
function diContainer($aliasOrClassName)
{
    static $services;
    $aliasOrClassName;
    
    if ($aliasOrClassName === '\samsonframework\di\tests\OtherTestClass' || $aliasOrClassName === 'otherTestModule') {
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
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\TestModuleClass' || $aliasOrClassName === 'testModule') {
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
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\OtherInterfaceTestClass' || $aliasOrClassName === 'testImplementsModule') {
        return new \samsonframework\di\tests\OtherInterfaceTestClass(
            '\samsonframework\di\tests\TestInterface',
            array(
                '0' => '1',
                '1' => '2',
                '2' => '3',
            ),
            'I am string'
        );
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\TestServiceClass' || $aliasOrClassName === 'testService') {
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
    } elseif ($aliasOrClassName === 'callbackTest' || $aliasOrClassName === 'closure056a8bd26a5f2a1.77653437') {
        if (true) {
            /*just for test*/
        }
        return new \samsonframework\di\tests\OtherTestClass(
            new \samsonframework\di\tests\OtherThirdTestClass(
                new \samsonframework\di\tests\OtherSecondTestClass()
            ),
            array('1'),
            '1'
        );
    }
}
