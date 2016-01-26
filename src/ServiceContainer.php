<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 26.01.16 at 10:33
 */
namespace samsonframework\di;

class ServiceContainer extends AbstractContainer
{
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
