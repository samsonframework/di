<?php
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
        // Merge other class constructor parameters
        $this->dependencies[$className] = array_merge($this->dependencies[$className], $parameters);

        // Store alias for this class name
        $this->aliases[$className] = $alias;
    }
}
