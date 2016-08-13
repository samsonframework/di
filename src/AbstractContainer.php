<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use Interop\Container\ContainerInterface;
use samsonframework\di\exception\ClassNotFoundException;
use samsonframework\di\exception\ContainerException;

/**
 * Abstract dependency injection container.
 *
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */
abstract class AbstractContainer implements ContainerInterface
{
    /** @var array[string] Collection of alias => class name for alias resolving */
    protected $aliases = array();

    /** @var array[string] Collection of class name dependencies trees */
    protected $dependencies = array();

    /** @var ContainerInterface[] Collection of delegated containers */
    protected $delegates = array();

    /** @var callable Dependency resolving function callable */
    protected $logicCallable;


    /**
     * Wrapper for calling dependency resolving function.
     *
     * @param string $dependency Dependency name
     *
     * @return mixed Created instance or null
     * @throws ContainerException
     */
    protected function logic($dependency)
    {
        if (!function_exists($this->logicCallable)) {
            throw new ContainerException('Logic function does not exists');
        }

        return call_user_func($this->logicCallable, $dependency);
    }

    /**
     * {@inheritdoc}
     */
    public function get($dependency)
    {
        // Get pointer from logic
        $module = $this->logic($dependency);

        // Try delegate lookup
        if (null === $module) {
            foreach ($this->delegates as $delegate) {
                try {
                    $module = $delegate->get($dependency);
                } catch (ContainerException $e) {
                    // Catch all delegated exceptions
                } catch (ClassNotFoundException $e) {
                    // Catch all delegated exceptions
                }
            }
        }

        if (null === $module) {
            throw new ClassNotFoundException($dependency);
        } else {
            return $module;
        }
    }

    /**
     * Implementing delegate lookup feature.
     * If current container cannot resolve entity dependency
     * resolving process is passed to delegated container.
     *
     * @param ContainerInterface $container Container for delegate lookup
     */
    public function delegate(ContainerInterface $container)
    {
        $this->delegates[] = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function has($dependency)
    {
        $found = array_key_exists($dependency, $this->dependencies)
            || in_array($dependency, $this->aliases, true);

        if (!$found) {
            foreach ($this->delegates as $delegate) {
                if ($delegate->has($dependency)) {
                    return true;
                }
            }
        }

        return $found;
    }

    /**
     * Set container dependency.
     *
     * @param mixed       $entity     Entity
     * @param string|null $alias      Entity alias for simplier finding
     * @param array       $parameters Collection of additional parameters
     *
     * @return self Chaining
     */
    abstract public function set($entity, $alias = null, array $parameters = array());
}
