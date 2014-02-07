<?php
namespace Mu\Kernel\Route\Dumper;

use Mu\Kernel;

class Nginx extends Kernel\Route\Dumper
{
	/**
	 * {@inheritDoc}
	 */
	public function dumpRoutes($routes)
	{
		$content = "location / {\n";
		$content .= "\troot " . PUBLIC_PATH . ";\n";
		$content .= "\tindex index.php;\n";
		/*foreach ($routes as $route) {
			$infos = $this->prepareRuleVars($route);
			$content .= "\trewrite " . $infos['pattern'] . ' ' . $infos['dest'] . " break;\n";
		}*/

		$content .= "\trewrite ^/favicon\\.ico$ /favicon.ico break;\n";
		$content .= "\trewrite ^.*\\.?(html|json)?$ /index.php?format=$1 break;\n\n";
		$content .= "\tfastcgi_pass   127.0.0.1:9001;\n";
		$content .= "\tfastcgi_index  index.php;\n";
		$content .= "\t" . 'fastcgi_param  SCRIPT_FILENAME   $document_root$fastcgi_script_name;' . "\n";
		$content .= "\tinclude        fastcgi_params;\n";
		$content .= '}';

		file_put_contents(PUBLIC_PATH . '/' . $this->getApp()->getName() . '.conf', $content);
	}

	/*
	 * @param Kernel\Route\Route $route
	 * @return array
	 */
	/*	public function prepareRuleVars(Kernel\Route\Route $route)
		{
			$pattern = $route->getPattern();
			$pattern = str_replace('/', '\/', '^' . $pattern . '\.?(' . implode('|', $route->getAllowedFormats()) . ')?$');
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

			$dest = '/index.php?' . implode('&', $vars);

			return array(
				'dest' => $dest,
				'pattern' => $pattern
			);
		}*/
}