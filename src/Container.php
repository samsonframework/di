<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use Interop\Container\ContainerInterface;
use samsonframework\di\exception\ContainerException;
use samsonframework\di\exception\NotFoundException;

/**
 * Class Container
 * @package samsonframework\di
 */
class Container implements ContainerInterface
{
    /** @var array[string] Collection of loaded modules into container */
    protected $modules = array();

    /** @var array[string] Collection of loaded modules into container stored by classes */
    protected $classes = array();

    public function set($id, $className)
    {
        // Check if we have not set this identifier/classname and it does exists
        if (!$this->has($className) && !$this->has($id) && class_exists($className)) {
            $class = new \ReflectionClass($className);

            /** @var array $dependencies Collection of dependent instances */
            $dependencies = array();

            /** @var bool $errors Flag that shows successfull dependencies loading */
            $errors = false;

            // Iterate all dependencies
            foreach ($class->getConstructor()->getParameters() as $parameter) {
                try {
                    $dependencyClass = $parameter->getClass()->name;

                    // Search for instance
                    if (!$this->has($dependencyClass)) {
                        // Go deeper in recursion
                        $this->set($dependencyClass, $dependencyClass);
                    }

                    // Store dependent instace
                    $dependencies[] = $this->get($dependencyClass);
                } catch(\Exception $e) {
                    // Failed loading some dependencies
                    $errors = true;
                }
            }

            if (!$errors) {
                // Create instance with dependencies
                $reflect = new \ReflectionClass($className);

                // Create instance with dependencies
                $instance = $reflect->newInstanceArgs($dependencies);

                // Store instance in collections
                $this->classes[strtolower($className)] = &$instance;
                $this->modules[$id] = &$instance;
            }
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        // Set pointer to module
        $module = &$this->modules[$id];

        if (null === $module) {
            throw new NotFoundException($id);
        } else {
            if (!is_object($module)) {
                throw new ContainerException($id);
            } else {
                return $module;
            }
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return isset($this->modules[$id]) || isset($this->classes[strtolower($id)]);
    }
}
