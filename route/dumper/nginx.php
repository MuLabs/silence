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
		foreach ($routes as $route) {
			$infos = $this->prepareRuleVars($route);
			$content .= "\trewrite " . $infos['pattern'] . ' ' . $infos['dest'] . " break;\n";
		}

		$content .= "\tfastcgi_pass   127.0.0.1:9000;\n";
		$content .= "\tfastcgi_index  index.php;\n";
		$content .= "\t" . 'fastcgi_param  SCRIPT_FILENAME   $document_root$fastcgi_script_name;' . "\n";
		$content .= "\tinclude        fastcgi_params;\n";
		$content .= '}';

		file_put_contents(PUBLIC_PATH . '/' . $this->getApp()->getName() . '.conf', $content);
	}
}