<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 26.01.16 at 15:11
 */
namespace samsonframework\di;

use samsonframework\di\exception\ClassNotFoundException;
use samsonframework\di\exception\ConstructorParameterNotSetException;

class Container extends AbstractContainer
{
    /** Variable name for storing services */
    const STATIC_COLLECTION_VARIABLE = '$services';

    /** @var array[string] Collection of loaded services */
    protected $services = array();

    /**
     * Get reflection parameter class name type hint if present without
     * auto loading and throwing exceptions.
     * TODO: Add resolving configured classes
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
     * Analyze class construtor dependencies and create tree for nested dependencies.
     *
     * @param \ReflectionMethod $constructor Reflection method __constructor instance
     * @param string $className
     * @param array  $dependencies Reference to tree for filling up
     *
     * @throws ClassNotFoundException
     */
    protected function buildConstructorDependencies(\ReflectionMethod $constructor, $className, &$dependencies)
    {
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
        // Resolve class name if present
        $className = array_key_exists($className, $this->resolver)
            ? $this->resolver[$className]
            : $className;

        // We need this class to exists to use reflections, it will try to autoload it also
        if (class_exists($className)) {
            $class = new \ReflectionClass($className);
            // We can build dependency tree only from constructor dependencies
            $constructor = $class->getConstructor();
            if (null !== $constructor) {
                $this->buildConstructorDependencies($constructor, $className, $dependencies);
            }
        } else { // Something went wrong and class is not auto loaded and missing
            throw new ClassNotFoundException($className);
        }

        return $dependencies;
    }

    /**
     * Set service dependency. Upon first creation of this class instance
     * it would be used everywhere where this dependency is needed.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return Container Chaining
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
     * @return Container Chaining
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
        $this->aliases[$className] = $alias;
    }

    /**
     * Generate initial class instance declaration
     * @param string $className Entity class name
     */
    protected function generateInitialDeclaration($className)
    {
        if (array_key_exists($className, $this->services)) {
            // Start service search or creation
            $this->generator
                ->text('isset('.self::STATIC_COLLECTION_VARIABLE.'[\''.$className.'\'])')
                ->newLine('? '.self::STATIC_COLLECTION_VARIABLE.'[\''.$className.'\']')
                ->newLine(': '.self::STATIC_COLLECTION_VARIABLE.'[\''.$className.'\'] = new '.$className.'(')
                ->tabs++;
        } else {
            // Start object instance creation
            $this->generator->text('new ' . $className . '(')->tabs++;
        }
    }

    /**
     * Parse class dependencies generated by dependency tree.
     *
     * @param mixed $dependency Dependency value
     * @param string $variable Dependency name
     * @param string $className
     * @param int $level Current recursion level
     *
     * @throws ConstructorParameterNotSetException
     */
    protected function parseClassDependencies($dependency, $variable, $className, $level)
    {
        // If dependency value is a string
        if (is_string($dependency)) {
            // Define if we have this dependency described in dependency tree
            $dependencyPointer = &$this->dependencies[$dependency];
            if (null !== $dependencyPointer) {
                // We have dependencies tree for this entity
                $this->generateCondition($dependency, $dependencyPointer, $level + 1);
            } elseif (class_exists($dependency, false)) {
                // There are no dependencies for this entity
                $this->generator->newLine('new ' . $dependency . '()');
            } else { // String variable
                $this->generator->newLine()->stringValue($dependency);
            }
        } elseif (is_array($dependency)) { // Dependency value is array
            $this->generator->newLine()->arrayValue($dependency);
        } elseif ($dependency === null) { // Parameter is not set
            throw new ConstructorParameterNotSetException($className . '::' . $variable);
        }
    }

    /**
     * Generate container dependency condition code.
     *
     * @param string $className
     * @param mixed  $dependencies
     * @param int    $level
     *
     * @throws ConstructorParameterNotSetException
     *
     */
    protected function generateCondition($className, &$dependencies, $level = 0)
    {
        $this->generator->newLine(($level === 0) ? 'return ' : '');

        $this->generateInitialDeclaration($className);

        // Get last dependency variable name
        end($dependencies);
        $last = key($dependencies);

        // Iterate all dependencies for this class
        foreach ($dependencies as $variable => $dependency) {
            $this->parseClassDependencies($dependency, $variable, $className, $level);

            // Add comma if this is not last dependency
            if ($variable !== $last) {
                $this->generator->text(',');
            }
        }
        $this->generator->tabs--;
        $this->generator->newLine(')' . ($level === 0 ? ';' : ''));
    }
}
