<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use samsonframework\di\exception\ClassNotFoundException;
use samsonframework\di\exception\ConstructorParameterNotSetException;
use samsonframework\di\exception\ContainerException;
use samsonframework\di\exception\NotFoundException;
use samsonphp\generator\Generator;


//TODO: Interface & abstract class resolving
//TODO: Lazy creation by default
//TODO: instance and passing to logic function

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

    /** @var array[string] Collection of class name dependencies trees */
    protected $dependencies = array();

    /** @var Generator */
    protected $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Get reflection paramater class name type hint if present without
     * autoloading and throwing exceptions.
     *
     * @param \ReflectionParameter $param Parameter for parsing
     *
     * @return string|null Class name typehint or null
     */
    protected function getClassName(\ReflectionParameter $param)
    {
        preg_match('/\[\s\<\w+?>\s(?<class>[\w\\\\]+)/', (string)$param, $matches);
        return array_key_exists('class', $matches) && $matches['class'] !== 'array'
            ? '\\' . ltrim($matches[1], '\\')
            : null;
    }

    /**
     * Recursively build class constructor dependencies tree.
     * TODO: Analyze recurrent dependencies and throw an exception
     *
     * @param string $className    Current class name for analyzing
     * @param array  $dependencies Reference to tree for filling up
     *
     * @return array [string] Multidimensional array as dependency tree
     * @throws ClassNotFoundException
     */
    protected function buildDependenciesTree($className, array &$dependencies)
    {
        // We need this class to exists to use reflections, it will try to autoload it also
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

                        // Set pointer to parameter as it can be set before
                        $parameterPointer = &$dependencies[$className][$parameter->getName()];

                        // If we have found dependency class
                        if ($dependencyClass !== null) {
                            // Point dependency class name
                            $parameterPointer = $dependencyClass;
                            // Go deeper in recursion and pass new branch there
                            $this->buildDependenciesTree($dependencyClass, $dependencies);
                        } elseif (!isset($parameterPointer)) { // Set null parameter value
                            $parameterPointer = null;
                        }

                    } else { // Stop iterating as first optional parameter is met
                        break;
                    }
                }
            }
        } else { // Something went wrong and class is not auto loaded and missing
            throw new ClassNotFoundException($className);
        }

        return $dependencies;
    }

    public function generateLogicConditions(array $dependencies, $class)
    {
        $this->generator->tabs++;

        $variables = array_keys($dependencies);
        for ($i = 0, $s = count($dependencies); $i < $s; $i++) {
            $className = $dependencies[$variables[$i]];
            if (is_string($className)) {
                // Define if we have this dependency described in dependency tree
                $dependencyPointer = &$this->dependencies[$className];
                if (isset($dependencyPointer)) {
                    $this->generator->newLine('new ' . $className . '(');
                    $this->generateLogicConditions($dependencyPointer, $className);
                    $this->generator->newLine(')');
                } elseif (class_exists($className, false)) {
                    $this->generator->newLine('new ' . $className . '()');
                } else { // String variable
                    $this->generator->newLine()->stringValue($className);
                }

                if ($i !== $s - 1) {
                    $this->generator->text(',');
                }
            } elseif (is_array($className)){ // Regular constructor parameter
                $this->generator->newLine()->arrayValue($className);

                if ($i !== $s - 1) {
                    $this->generator->text(',');
                }
            } elseif ($className === null) { // Parameter is not set
                throw new ConstructorParameterNotSetException($class.'::'.$variables[$i]);
            }


        }
        $this->generator->tabs--;
    }

    public function generateLogicFunction($functionName = 'diContainer')
    {
        $inputVariable = '$aliasOrClassName';
        $this->generator
            ->defFunction($functionName, array($inputVariable))
            ->defVar('static $services')
        ;

        $started = false;
        foreach ($this->dependencies as $alias => $dependency) {
            // Get class name from alias
            $className = array_key_exists($alias, $this->aliases) ? $this->aliases[$alias] : $alias;

            // Generate condition statement to define if this class is needed
            if (!$started) {
                $started = true;
                $this->generator->defIfCondition($inputVariable . ' === \'' . $alias . '\'');
            } else {
                $this->generator->defElseIfCondition($inputVariable . ' === \'' . $alias . '\'');
            }

            $this->generator->newLine('return new '.$className.'(');

            $this->generateLogicConditions($dependency, $className);

            $this->generator->newLine(');');
        }

        // Add method not found
        $this->generator->endIfCondition()->newLine('return null;')->endFunction();

        return $this->generator->flush();
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
        $module = diContainer($alias);

        if (null === $module) {
            throw new NotFoundException($alias);
        } else {
            if (!is_object($module)) {
                throw new ContainerException($alias);
            } else {
                return $module;
            }
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $alias Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($alias)
    {
        return array_key_exists($alias, $this->services)
        || array_key_exists($alias, $this->aliases);
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
        // Add this class dependencies to dependency tree
        $this->dependencies = array_merge(
            $this->dependencies,
            $this->buildDependenciesTree($className, $this->dependencies)
        );

        // Merge other class constructor parameters
        $this->dependencies[$className] = array_merge($this->dependencies[$className], $parameters);

        // Store alias for this class name
        $this->aliases[$alias] = $className;
    }
}
