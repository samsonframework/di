# SamsonFramework DI package
 
SamsonFramework dependency injection container implementation

[![Latest Stable Version](https://poser.pugx.org/samsonframework/di/v/stable.svg)](https://packagist.org/packages/samsonframework/di)
[![Build Status](https://scrutinizer-ci.com/g/samsonframework/di/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samsonframework/di/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/samsonframework/di/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samsonframework/di/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/samsonframework/di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/samsonframework/di/?branch=master) 
[![Total Downloads](https://poser.pugx.org/samsonframework/di/downloads.svg)](https://packagist.org/packages/samsonframework/di)
[![Stories in Ready](https://badge.waffle.io/samsonframework/di.png?label=ready&title=Ready)](https://waffle.io/samsonframework/di)

##Main features:
 * The fastest possible dependency injection container implementation.
 * Supports regular constructor parameters combined with type hinted dependencies.
 * Supports closures as dependencies.
 * Supports services - singleton object instance that needs to be created only once.
 * Implements [container-interop](https://github.com/container-interop/container-interop)(PSR-11).
 * Implements [container-interop delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md).
 
## TODO
 * ```25/01/2016``` DOES NOT(yet) support setter injections.
 * ```25/01/2016``` DOES NOT(yet) support existing instances as dependencies.
 
##Auto-wiring
We do not support these feature as we suppose that your software using this awesome package should declare
which entities need resolving using ```set()``` method. But feel free to create a ```AutoWireContainer``` that
will get all declared classes, find ones created by user and call ```set()``` for them.

>I really do not think that you need to auto-wire all existing classes.
 
##Why we are the fastest
### Concept = Programmable logic
All our packages have same goal in mind - [Programmable logic](https://en.wikipedia.org/wiki/Programmable_logic_device).
We are trying to create system that can be easily configurable and usable and then using best practises, knowing all internal
logic and structure of a package, generate maximum possible performant PHP code to repeat same logic. This generated
code is meant to be used only in production environment and guarantees 100% logic and functionality compatibility with source code.

### Simple language
This implementation is lacking all other needed and awesome features like injectors and configurations(for now), but
this is fastest possible dependency injection implementation. The core of its performance is generated PHP code. We perform
static analysis of defined entities and their dependencies and generate PHP code logic function. Here is an example:
```php
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
    } elseif ($aliasOrClassName === 'callbackTest' || $aliasOrClassName === 'closure056a825fd358d50.68225741') {
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
```

##Extending package
This container can be easily extended using [container-interop delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md).
So you can implement container-interop ```ContainerInterface``` and pass it to ```$container->delegate($yourContainer)```, this approach was used for
creating Closure implementation(```ClosureContainer```).

##Closure implementation
The idea behind closure implementation is that you create all dependencies manually there so we simply parse
your closure code and just insert it "as it is" into logic function by its alias(see last if branch in example).
> We do not support parameters as this callback are ment not to have them
Also we are not supporting ```use``` closure syntax as it should be using context from a file that declares closure.

##Installation
You can install this package through Composer:
```composer require samsonframework/di```

The packages adheres to the SemVer specification, and there will be full backward compatibility between minor versions.

##Testing
```$ vendor/bin/phpunit```

##Contributing
Feel free to fork and create pull requests at any time.

##Security
If you discover any security related issues, please use this repository issue tracker.

##License
Open Software License ("OSL") v 3.0. Please see License File for more information.

[SamsonOS](http://samsonos.com)
