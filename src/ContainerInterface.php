<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 24.01.16 at 15:18
 */
namespace samsonframework\di;

/**
 * Dependency injection container.
 * @package samsonframework\di
 */
interface ContainerInterface extends \Interop\Container\ContainerInterface
{
    /**
     * Set dependency alias with callback function.
     *
     * @param callable $callable Callable to return dependency
     * @param string $alias Dependency name
     *
     * @return self Chaining
     */
    public function callback($callable, $alias = null);

    /**
     * Set service dependency. Upon first creation of this class instance
     * it would be used everywhere where this dependency is needed.
     *
     * @param string $className Fully qualified class name
     * @param string $alias Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return self Chaining
     */
    public function service($className, $alias = null, array $parameters = array());

    /**
     * Set service dependency by passing object instance.
     *
     * @param mixed $instance Instance that needs to be return by this dependency
     * @param string $alias Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return self Chaining
     */
    public function instance(&$instance, $alias = null, array $parameters = array());

    /**
     * Set dependency.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return ContainerInterface Chaining
     */
    public function set($className, $alias = null, array $parameters = array());
}
