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
    protected function getSpaces($line)
    {
        preg_match('/(\s+)[^\s]/', $line, $matches);
        return $matches[1];
    }
    /**
     * Generate container dependency condition code.
     * @param string    $alias Entity alias
     * @param mixed     $entity Entity
     */
    public function generateCondition($alias, &$entity)
    {
        // Get closure reflection
        $reflection = new \ReflectionFunction($entity);
        // Read closure file
        $lines = file($reflection->getFileName());

        $indentation = $this->getSpaces($lines[$reflection->getStartLine()]);

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

            // Cut only base $indentation to beautify output
            $spaces = substr($this->getSpaces($lines[$l]), strlen($indentation));

            // Add closure code
            $this->generator->newLine($spaces.trim($lines[$l]));
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
    public function set($entity, array $parameters, $alias = null)
    {
        // Add unique closure alias
        $this->aliases[$alias] = 'closure' . uniqid(0, 99999);

        // Store parameters
        $this->parameters[$alias] = $parameters;

        // Store dependency
        $this->dependencies[$alias] = $entity;
    }
}
