<?php declare(strict_types=1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

/**
 * Dependency injection container interface.
 *
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */
interface ContainerInterface extends \Interop\Container\ContainerInterface
{
    /**
     * Set container dependency.
     *
     * @param mixed       $entity       Entity
     * @param string|null $alias        Entity alias for simplier finding
     * @param array       $dependencies Collection of additional parameters
     *
     * @return ContainerInterface Chaining
     */
    public function set($entity, string $alias = null, array $dependencies = []) : ContainerInterface;

    /**
     * Set service dependency. Upon first creation of this class instance
     * it would be used everywhere where this dependency is needed.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return ContainerInterface Chaining
     */
    public function service($className, string $alias = null, array $parameters = []) : ContainerInterface;
}
