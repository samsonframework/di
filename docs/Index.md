#SamsonFramework Dependency Injection package documentation

##Main features:
 * The fastest possible dependency injection container implementation.
 * Supports closures as dependencies.
 * Supports services - singleton object instance that needs to be created only once.
 * Implements [container-interop](https://github.com/container-interop/container-interop)(PSR-11).
 * Implements [container-interop delegate lookup feature](https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup.md).
 
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
function container($aliasOrClassName)
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

