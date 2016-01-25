<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use samsonframework\di\exception\ContainerException;
use samsonframework\di\exception\NotFoundException;

//TODO: caching
//TODO: Interface & abstract class resolving
//TODO: Other parameter types(not hintable) resolving
//TODO: Lazy creation by default

/**
 * Class Container
 * @package samsonframework\di
 */
class Container implements ContainerInterface
{
    /** @var array[string] Collection of loaded services */
    protected $services = array();

    /** @var array[string] Collection of alias => class name for alias resolving*/
    protected $aliases = array();

    protected function getClassName(\ReflectionParameter $param) {
        preg_match('/\[\s\<\w+?>\s([\w\\\\]+)/s', $param->__toString(), $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Recursively build class constructor dependencies tree.
     *
     * @param string $className Current class name for analyzing
     * @param array  $dependencies Reference to tree for filling up
     *
     * @return array[string] Multidimensional array as dependency tree
     */
    protected function buildDependenciesTree($className, array &$dependencies = array())
    {
        // We need this class to exists to use reflections
        if (class_exists($className)) {
            $class = new \ReflectionClass($className);
            // We can build dependency tree only from constructor dependencies
            $constructor = $class->getConstructor();
            if (null !== $constructor) {
                // Iterate all dependencies
                foreach ($constructor->getParameters() as $parameter) {
                    // Ignore optional parameters
                    if (!$parameter->isOptional()) {
                        // Read dependency class name
                        $dependencyClass = $this->getClassName($parameter);

                        // If we have found dependency class
                        if ($dependencyClass !== null) {
                            // Store new inner dependency branch
                            $dependencies[$className][$parameter->getName()] = array($dependencyClass => array());
                            // Go deeper in recursion and pass new branch there
                            $this->buildDependenciesTree(
                                $dependencyClass,
                                $dependencies[$className][$parameter->getName()]
                            );
                        }

                    } else { // Stop iterating as first optional parameter is met
                        break;
                    }
                }
            }
        }

        return $dependencies;
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
        $module = &$this->services[$id];

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
        return isset($this->services[$id]) || isset($this->aliases[$id]);
    }

    /**
     * Set dependency alias with callback function.
     *
     * @param callable $callable Callable to return dependency
     * @param string   $alias    Dependency name
     *
     * @return self Chaining
     */
    public function callback($callable, $alias = null)
    {
        // TODO: Implement callback() method.
    }

    /**
     * Set service dependency. Upon first creation of this class instance
     * it would be used everywhere where this dependency is needed.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return self Chaining
     */
    public function service($className, $alias = null, array $parameters = array())
    {
        // TODO: Implement service() method.
    }

    /**
     * Set service dependency by passing object instance.
     *
     * @param mixed  $instance   Instance that needs to be return by this dependency
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return self Chaining
     */
    public function instance(&$instance, $alias = null, array $parameters = array())
    {

        // TODO: Implement instance() method.
    }

    /**
     * Set dependency.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return ContainerInterface Chaining
     */
    public function set($className, $alias = null, array $parameters = array())
    {
        $tree = $this->buildDependenciesTree($className);

        var_dump($tree);
    }
}
