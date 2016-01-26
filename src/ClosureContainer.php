<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>.
 * on 26.01.16 at 10:32
 */
namespace samsonframework\di;

/**
 * Container for resolving closure dependencies.
 *
 * @package samsonframework\di
 */
class ClosureContainer extends AbstractContainer
{
    public function getLogicConditions()
    {
        foreach ($this->dependencies as $alias => $callback) {
            // Get closure reflection
            $reflection = new \ReflectionFunction($callback);
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
    }

    /**
     * Set container dependency.
     *
     * @param callable    $entity     Callable
     * @param string|null $alias      Entity alias for simplier finding
     * @param array       $parameters Collection of additional parameters
     *
     * @return self Chaining
     */
    public function set($entity, $alias = null, array $parameters = array())
    {
        // Add unique closure alias
        $this->aliases[$alias] = 'closure' . uniqid(0, 99999);

        // Store parameters
        $this->parameters[$alias] = $parameters;

        // Store dependency
        $this->dependencies[$alias] = $entity;
    }
}
