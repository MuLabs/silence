<?php
namespace Beable\Kernel\Route\Dumper;

use Beable\Kernel;

class Apache extends Kernel\Route\Dumper
{
	/**
	 * {@inheritDoc}
	 */
	public function dumpRoutes($routes)
	{
		$content = "RewriteEngine On\n\n";

		foreach ($routes as $route) {
			$infos = $this->prepareRuleVars($route);
			$content .= 'RewriteRule ' . $infos['pattern'] . ' ' . $infos['dest'] . " [L,QSA]\n";
		}

		file_put_contents(PUBLIC_PATH . '/.htaccess', $content);
	}
}