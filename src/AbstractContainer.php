<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 22.01.16 at 23:53
 */
namespace samsonframework\di;

use Interop\Container\ContainerInterface;
use samsonframework\di\exception\ClassNotFoundException;
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

    /** @var callable Logic function */
    protected $logicCallable;

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
        if (!function_exists($this->logicCallable)) {
            throw new ContainerException('Logic function does not exists');
        }

        return call_user_func($this->logicCallable, $alias);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $alias Identifier of the entry to look for.
     *
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
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
                } catch (ClassNotFoundException $e) {
                    // Catch all delegated exceptions
                }
            }
        }

        if (null === $module) {
            throw new ClassNotFoundException($alias);
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
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $alias Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($alias)
    {
        $found = array_key_exists($alias, $this->dependencies)
            || in_array($alias, $this->aliases);

        if (!$found) {
            foreach ($this->delegates as $delegate) {
                if ($delegate->has($alias)) {
                    return true;
                }
            }
        }

        return $found;
    }

    /**
     * Generate logic conditions and their implementation for container and its delegates.
     *
     * @param string     $inputVariable Input condition parameter variable name
     * @param bool|false $started       Flag if condition branching has been started
     */
    public function generateConditions($inputVariable = '$alias', $started = false)
    {
        // Iterate all container dependencies
        foreach ($this->dependencies as $alias => $entity) {
            // Generate condition statement to define if this class is needed
            $conditionFunc = !$started ? 'defIfCondition' : 'defElseIfCondition';

            // Create condition branch
            $condition = $inputVariable . ' === \'' . $alias . '\'';
            // If we have an alias for this - add it to condition
            $condition .= array_key_exists($alias, $this->aliases)
                ? ' || ' . $inputVariable . ' === \'' . $this->aliases[$alias] . '\''
                : '';
            // Output condition branch
            $this->generator->$conditionFunc($condition);

            // Generate condition for each dependency
            $this->generateCondition($alias, $entity);

            // Set flag that condition is started
            $started = true;
        }

        /** @var self $delegate Iterate delegated container to get their conditions */
        foreach ($this->delegates as $delegate) {
            // Set current generator
            if ($delegate instanceof AbstractContainer) {
                $delegate->generator = $this->generator;
            }
            $delegate->generateConditions($inputVariable, $started);
        }
    }

    /**
     * Generate dependency injection logic function.
     *
     * @param string $functionName
     *
     * @return string PHP logic function code
     */
    public function generateFunction($functionName = self::LOGIC_FUNCTION_NAME)
    {
        // Store logic callable
        $this->logicCallable = $functionName;

        $inputVariable = '$aliasOrClassName';
        $this->generator
            ->defFunction($functionName, array($inputVariable))
            ->defVar('static $services')
            ->newLine();

        // Generate all container and delegate conditions
        $this->generateConditions($inputVariable, false);

        // Add method not found
        return $this->generator
            ->endIfCondition()
            ->endFunction()
            ->flush();
    }

    /**
     * Set container dependency.
     *
     * @param mixed       $entity     Entity
     * @param array       $parameters Collection of additional parameters
     * @param string|null $alias      Entity alias for simple finding
     *
     * @return $this Chaining
     */
    abstract public function set($entity, array $parameters, $alias = null);

    /**
     * Generate container dependency condition code.
     *
     * @param string $alias  Entity alias
     * @param mixed  $entity Entity
     */
    abstract protected function generateCondition($alias, &$entity);
}
