<?php declare(strict_types=1);
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 26.01.16 at 15:11
 */
namespace samsonframework\di;

/**
 * Dependency container.
 *
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */
class Container extends AbstractContainer
{
    /** @var array[string] Collection of loaded services */
    protected $services = array();

    /**
     * Set service dependency. Upon first creation of this class instance
     * it would be used everywhere where this dependency is needed.
     *
     * @param string $className  Fully qualified class name
     * @param string $alias      Dependency name
     * @param array  $parameters Collection of parameters needed for dependency creation
     *
     * @return $this Chaining
     */
    public function service($className, string $alias = null, array $parameters = []) : Container
    {
        $this->services[$className] = $className;

        return $this->set($className, $alias, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function set($className, string $alias = null, array $dependencies = []) : Container
    {
        // Merge other class constructor parameters
        $this->dependencies[$className] = array_merge($this->dependencies[$className], $dependencies);

        // Store alias for this class name
        $this->aliases[$className] = $alias;
    }
}
