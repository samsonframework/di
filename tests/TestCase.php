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
     * Create container logic executable.
     *
     * @param string $function Logic function name
     */
    protected function createLogic($function = 'container')
    {
        // Create logic and import it
        $logic = $this->container->build($function);
        $path = __DIR__.'/ContainerLogic.php';
        @unlink($path);
        file_put_contents($path, '<?php '.$logic);
        require $path;
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
