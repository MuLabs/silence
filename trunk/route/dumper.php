<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

abstract class Dumper extends Kernel\Core
{
    /**
     * @param Route[] $routes
     * @return
     */
    abstract protected function dumpRoutes($routes);

    abstract public function dumpSites($sites);

    /**
     * @param Kernel\Route\Route $route
     * @return array
     */
    public function prepareRuleVars(Kernel\Route\Route $route)
    {
        $pattern = $route->getPattern();
        $pattern = str_replace('/', '\/', '^' . $pattern . '$');
        $vars = array(
            'rn=' . $route->getName()
        );

        $defaultVars = $route->getDefaultVars();
        while (preg_match('#\{([^\}\/]+)}#iu', $pattern, $matches)) {
            $match = $matches[1];

            if (isset($defaultVars[$match])) {
                $pattern = str_replace('{' . $match . '}\/', '([^\/\.]+)?\/?', $pattern);
            }
            $pattern = str_replace('{' . $match . '}', '([^\/\.]+)', $pattern);
            $vars[] = $match . '=$' . count($vars);
        }

        $dest = '/index.php?' . implode('&', $vars);

        return array(
            'dest' => $dest,
            'pattern' => $pattern
        );
    }
}