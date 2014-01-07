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
		foreach ($routes as $route) {
			$infos = $this->prepareRuleVars($route);
			$content .= "rewrite " . $infos['pattern'] . ' ' . $infos['dest'] . " break;\n";
		}
		$content .= '}';

		file_put_contents(PUBLIC_PATH . '/nginx.conf', $content);
	}
}