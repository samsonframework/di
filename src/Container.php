<?php declare(strict_types=1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 26.01.16 at 15:11
 */
namespace samsonframework\di;
use samsonframework\di\exception\ClassNotFoundException;
use samsonframework\di\exception\ContainerException;

/**
 * Dependency container.
 *
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */
class Container implements ContainerInterface
{
    /** @var array[string] Collection of loaded services */
    protected $services = [];

    /** @var array[string] Collection of alias => class name for alias resolving */
    protected $aliases = [];

    /** @var array[string] Collection of class name dependencies trees */
    protected $dependencies = [];

    /** @var ContainerInterface[] Collection of delegated containers */
    protected $delegates = [];

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
        if (!is_callable($this->logicCallable)) {
            throw new ContainerException('Logic function is not callable');
        }

        return call_user_func($this->logicCallable, $dependency);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \samsonframework\di\exception\ContainerException
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
    public function has($dependency) : bool
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
     * Set service dependency. Upon first creation of this class instance
     * it would be used everywhere where this dependency is needed.
     *
     * @param string $className  Fully qualified class name
     * @param array  $parameters Collection of parameters needed for dependency creation
     * @param string $alias      Dependency name
     *
     * @return ContainerInterface Chaining
     */
    public function service($className, array $parameters = [], string $alias = null) : ContainerInterface
    {
        $this->services[$className] = $className;

        return $this->set($className, $parameters, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function set($className, array $dependencies = [], string $alias = null) : ContainerInterface
    {
        // Create dependencies collection for class name
        if (!array_key_exists($className, $this->dependencies)) {
            $this->dependencies[$className] = [];
        }
        
        // Merge other class constructor parameters
        $this->dependencies[$className] = array_merge($this->dependencies[$className], $dependencies);

        // Store alias for this class name
        $this->aliases[$className] = $alias;

        return $this;
    }
}
