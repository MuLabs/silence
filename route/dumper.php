<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

abstract class Dumper extends Kernel\Core
{
    /**
     * @param Route[] $routes
     * @param $lang
     * @return mixed
     */
    abstract protected function dumpRoutes($routes, $lang = null);

    abstract public function dumpSites($sites);

    abstract public function moveFile($content);

    /**
     * @param Kernel\Route\Route $route
     * @param null $lang
     * @return array
     */
    public function prepareRuleVars(Kernel\Route\Route $route, $lang = null)
    {
        $pattern = $route->getPattern();
        $pattern = str_replace('/', '\/', '^' . ($lang ? '/' . $lang : '') . $pattern . '$');
        $vars = array(
            'rn=' . $route->getName()
        );

        $defaultVars = $route->getDefaultVars();
        while (preg_match('#\{([^\}\/]+)}#iu', $pattern, $matches)) {
            $match = $matches[1];

            if (isset($defaultVars[$match])) {
                $pattern = str_replace('{' . $match . '}\/', '(?<'.$match.'>[^\/\.]+)?\/?', $pattern);
            }
            $pattern = str_replace('{' . $match . '}', '(?<'.$match.'>[^\/\.]+)', $pattern);
            $vars[] = $match . '=$' . count($vars);
        }

        $dest = '/index.php?' . implode('&', $vars);

        return array(
            'dest' => $dest,
            'pattern' => $pattern
        );
    }
}