<?php
namespace Mu\Kernel\Route;

use Mu\Kernel;

abstract class Dumper extends Kernel\Core
{
	/**
	 * @param Route[] $routes
	 */
	abstract public function dumpRoutes($routes);

	/**
	 * @param Route $route
	 * @return array
	 */
	public function prepareRuleVars(Route $route)
	{
		$pattern = $route->getPattern();
		if (strlen($pattern) && $pattern{0} == '/') {
			$pattern = substr($pattern, 1);
		}
		$pattern = str_replace('/', '\/', '^' . $pattern . '\.?('.implode('|',$route->getAllowedFormats()).')?$');
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

		// Add format var:
		$vars[] = 'format=$' . count($vars);

		$dest = 'index.php?' . implode('&', $vars);

		return array(
			'dest' => $dest,
			'pattern' => $pattern
		);
	}
}