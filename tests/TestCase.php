<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 08.08.16 at 14:24
 */
namespace samsonframework\di\tests;

use samsonframework\di\Container;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Container */
    protected $container;

    /** @var string Service alias */
    protected $testServiceAlias = 'test_service';

    /**
     * Get $object private/protected property value.
     *
     * @param string $property Private/protected property name
     *
     * @param object $object   Object instance for getting private/protected property value
     *
     * @return mixed Private/protected property value
     */
    protected function getProperty($property, $object)
    {
        $property = (new \ReflectionClass($object))->getProperty($property);
        $property->setAccessible(true);
        try {
            return $property->getValue($object);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set $object private/protected property value.
     *
     * @param string $property Private/protected property name
     *
     * @param object $object   Object instance for getting private/protected property value
     *
     * @param mixed  $value Value for property setting
     *
     * @return mixed Private/protected property value
     */
    protected function setProperty($property, $object, $value)
    {
        $property = (new \ReflectionClass($object))->getProperty($property);
        $property->setAccessible(true);
        try {
            return $property->setValue($object, $value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set container dependencies
     */
    protected function setContainerDependencies()
    {
        $this->container->service(
            TestServiceClass::class,
            [
                'dependency1' => TestModuleClass::class,
                'arrayParam' => [1,2,3],
                'stringParam' => 'I am string'
            ],
            $this->testServiceAlias
        );

        $this->container->set(
            TestModuleClass::class,
            [
                'dependency1' => OtherTestClass::class,
                'dependency2' => OtherSecondTestClass::class,
                'arrayParam' => [1,2,3],
                'stringParam' => 'I am string'
            ]
        );

        $this->container->set(
            OtherTestClass::class,
            [
                'dependency1' => OtherThirdTestClass::class,
                'arrayParam' => [2,1,2,3],
                'stringParam' => 'I am string2'
            ]
        );

        $this->container->set(
            OtherThirdTestClass::class,
            [
                'dependency1' => OtherSecondTestClass::class
            ]
        );

        $this->container->set(OtherSecondTestClass::class, []);
    }
}
