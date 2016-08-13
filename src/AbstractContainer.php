<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use Interop\Container\ContainerInterface;
use samsonframework\di\exception\ContainerException;
use samsonframework\di\exception\NotFoundException;
use samsonphp\generator\Generator;

/**
 * Abstract dependency injection container.
 *
 * @package samsonframework\di
 */
abstract class AbstractContainer implements ContainerInterface
{
    /** Default logic function name */
    const LOGIC_FUNCTION_NAME = 'diContainer';

    /** @var array[string] Collection of alias => class name for alias resolving */
    protected $aliases = array();

    /** @var array[string] Collection of entity name resolving */
    protected $resolver = array();

    /** @var array[string] Collection of class name dependencies trees */
    protected $dependencies = array();

    /** @var ContainerInterface[] Collection of delegated containers */
    protected $delegates = array();

    /** @var array[string] Collection of dependency parameters */
    protected $parameters = array();

    /** @var \samsonphp\generator\Generator */
    protected $generator;

    /**
     * Container constructor.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Help container resolving interfaces and abstract classes or any entities to
     * different one.
     *
     * @param string $source      Source entity name
     * @param string $destination Destination entity name
     *
     * @return self Chaining
     */
    public function resolve($source, $destination)
    {
        $this->resolver[$source] = $destination;

        return $this;
    }

    /**
     * Internal logic handler. Calls generated logic function
     * for performing entity creation or search. This is encapsulated
     * method for further overriding.
     *
     * @param string $alias Entity alias
     *
     * @return mixed Created instance or null
     * @throws ContainerException
     */
    protected function logic($alias)
    {
        if (!function_exists(self::LOGIC_FUNCTION_NAME)) {
            throw new ContainerException('Logic function does not exists');
        }

        return call_user_func(self::LOGIC_FUNCTION_NAME, $alias);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $alias Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($alias)
    {
        // Get pointer from logic
        $module = $this->logic($alias);

        // Try delegate lookup
        if (null === $module) {
            foreach ($this->delegates as $delegate) {
                try {
                    $module = $delegate->get($alias);
                } catch (ContainerException $e) {
                    // Catch all delegated exceptions
                } catch (NotFoundException $e) {
                    // Catch all delegated exceptions
                }
            }
        }

        if (null === $module) {
            throw new NotFoundException($alias);
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
     * Define if container has dependency.
     *
     * @param string $dependency Dependency class name or alias
     *
     * @return bool True if container has requested dependency
     */
    public function has($dependency)
    {
        $found = array_key_exists($dependency, $this->dependencies)
            || in_array($dependency, $this->aliases, true);

        // Delegate searching if not found
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
