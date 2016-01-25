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
 * ```25/01/2016``` This class supports only constructor dependency injections
 * Class supports regular constructor parameters combined with type hinted dependencies.
 * Class supports service - singleton object instance that needs to be created only once.
 * Class implements PSR-11([container-interop](https://github.com/container-interop/container-interop)).
 
###Why we are the fastest
This implementation is lacking all other needed and awesome features like injectors and configurations(for now), but
this is fastest possible dependency injection implementation. The core of its performance is generated PHP code. We perform
static analysis of defined entities and their dependencies and generate PHP code logic function. Here is an example:
```php
function diContainer($aliasOrClassName)
{
    static $services;
    
    if ($aliasOrClassName === '\samsonframework\di\tests\OtherTestClass') {
        return 
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
        );
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\OtherThirdTestClass') {
        return 
        new \samsonframework\di\tests\OtherThirdTestClass(
            new \samsonframework\di\tests\OtherSecondTestClass()
        );
    } elseif ($aliasOrClassName === '\samsonframework\di\tests\TestModuleClass') {
        return 
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
        );
    }
}
```

##Installation
You can install this package through Composer:

```composer require samsonframework/di```

The packages adheres to the SemVer specification, and there will be full backward compatibility between minor versions.

##Testing

```$ vendor/bin/phpunit```

##Contributing

Feel free to form and create pull requests at any time.

##Security

If you discover any security related issues, please use this repository issue tracker.

[SamsonOS](http://samsonos.com)
