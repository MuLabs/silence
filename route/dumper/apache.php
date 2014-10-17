<?php
namespace Mu\Kernel\Route\Dumper;

use Mu\Kernel;

class Apache extends Kernel\Route\Dumper
{
	/**
	 * {@inheritDoc}
	 */
	public function dumpRoutes($routes)
	{
		$content = "RewriteEngine On\n\nRewriteRule ^favicon.ico$ - [L]\n\n";

		/*foreach ($routes as $route) {
			$infos = $this->prepareRuleVars($route);
			$content .= 'RewriteRule ' . $infos['pattern'] . ' ' . $infos['dest'] . " [L,QSA]\n";
		}*/
		$content .= "RewriteRule ^.*\\.?(html|json)?$ index.php [L,QSA]";

		file_put_contents(PUBLIC_PATH . '/.htaccess', $content);
	}
}