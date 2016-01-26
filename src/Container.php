<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use samsonframework\di\exception\ClassNotFoundException;
use samsonframework\di\exception\ConstructorParameterNotSetException;
use samsonphp\generator\Generator;

//TODO: Interface & abstract class resolving.
//TODO: Lazy creation by default, need to use Mocks and magic methods.
//TODO: existing instances passing to logic function.
//TODO: closure and other fully qualified name resolving, probably using tokenizer.
//TODO: Separate generator to a new class.
//TODO: Separate instance, service, class, closure containers using delegate interface.

/**
 * Dependency injection container.
 *
 * @package samsonframework\di
 */
class Container extends AbstractContainer
{
    /** @var array[string] Collection of alias => closure for alias resolving */
    protected $callbacks = array();

    /** @var array[string] Collection of loaded services */
    protected $services = array();

    /** @var Generator */
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
     * Get reflection paramater class name type hint if present without
     * auto loading and throwing exceptions.
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

    /**
     * Recursive object creation with dependencies.
     *
     * @param array  $dependencies Collection of current class dependenices
     * @param string $class        Current class name
     *
     * @throws ConstructorParameterNotSetException
     */

    public function generateLogicConditions(array &$dependencies, $class)
    {
        // Start service creation
        if (array_key_exists($class, $this->services)) {
            $this->generator->newLine('isset($services[\''.$class.'\'])');
            $this->generator->newLine('? $services[\''.$class.'\']');
            $this->generator->newLine(': $services[\''.$class.'\'] = new '.$class.'(');
        } else { // Regular entity creation
            $this->generator->newLine('new ' . $class . '(');
        }
        $this->generator->tabs++;

        // Get last dependency variable name
        end($dependencies);
        $last = key($dependencies);

        // Iterate all dependencies for this class
        foreach ($dependencies as $variable => $dependency) {
            // If dependency value is a string
            if (is_string($dependency)) {
                // Define if we have this dependency described in dependency tree
                $dependencyPointer = &$this->dependencies[$dependency];
                if (null !== $dependencyPointer) {
                    // We have dependencies tree for this entity
                    $this->generateLogicConditions($dependencyPointer, $dependency);
                } elseif (class_exists($dependency, false)) {
                    // There are no dependencies for this entity
                    $this->generator->newLine('new ' . $dependency . '()');
                } else { // String variable
                    $this->generator->newLine()->stringValue($dependency);
                }
            } elseif (is_array($dependency)) { // Dependency value is array
                $this->generator->newLine()->arrayValue($dependency);
            } elseif ($dependency === null) { // Parameter is not set
                throw new ConstructorParameterNotSetException($class . '::' . $variable);
            }

            // Add comma if this is not last dependency
            if ($variable !== $last) {
                $this->generator->text(',');
            }
        }
        $this->generator->tabs--;
        $this->generator->newLine(')');
    }

    /**
     * Generate callback logic implementation as plain code.
     *
     * @param string $className Callback alias
     */
    protected function generateCallback($className)
    {
        // Get closure reflection
        $reflection = new \ReflectionFunction($this->callbacks[$className]);
        // Read closure file
        $lines = file($reflection->getFileName());
        $opened = 0;
        // Read only closure lines
        for ($l = $reflection->getStartLine(); $l < $reflection->getEndLine(); $l++) {
            // Fix opening braces scope
            if (strpos($lines[$l], '{') !== false) {
                $opened++;
            }

            // Fix closing braces scope
            if (strpos($lines[$l], '}') !== false) {
                $opened--;
            }

            // Break if we reached closure end
            if ($opened === -1) {
                break;
            }

            // Add closure code
            $this->generator->newLine(trim($lines[$l]));
        }
    }

    /**
     * @param string $functionName
     *
     * @return string
     * @throws ConstructorParameterNotSetException
     */
    public function generateLogicFunction($functionName = self::LOGIC_FUNCTION_NAME)
    {
        $inputVariable = '$aliasOrClassName';
        $this->generator
            ->defFunction($functionName, array($inputVariable))
            ->defVar('static $services')
        ->newLine();

        reset($this->dependencies);
        $first = key($this->dependencies);

        foreach ($this->dependencies as $className => $dependencies) {
            // Generate condition statement to define if this class is needed
            $conditionFunc = $className === $first ? 'defIfCondition' : 'defElseIfCondition';
            $this->generator->$conditionFunc($inputVariable . ' === \'' . $className . '\'');

            // If this is a callback entity
            if (array_key_exists($className, $this->callbacks)) {
                $this->generateCallback($className);
            } else { // Regular entity
                $this->generator->newLine('return ');

                // Go to recursive dependencies definition
                $this->generateLogicConditions($dependencies, $className);

                // Close top level instance creation
                $this->generator->text(';');
            }
        }

        // Add method not found
        return $this->generator
            ->endIfCondition()
            ->endFunction()
            ->flush();
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
        // Add unique closure alias
        $this->aliases[$alias] = 'closure'.uniqid(0, 99999);

        // Store callback
        $this->callbacks[$alias] = $callable;

        // Store dependency
        $this->dependencies[$alias] = $callable;
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
        $this->services[$className] = $className;

        return $this->set($className, $alias, $parameters);
    }

    /**
     * Set dependency.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return self Chaining
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
