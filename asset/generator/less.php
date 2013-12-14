<?php
namespace Beable\Kernel\Asset\Generator;

use Beable\Kernel;

class Less extends Kernel\Asset\Generator\Css
{
	/**
	 * @return int
	 */
	public function generateAsset()
	{
		require VENDOR_PATH . '/leafo/lessphp/lessc.inc.php';

		$less = new \lessc();
		$content = $less->compile($this->getFullContent());
		$content = $this->minify($content);
		$path = $this->getAsset()->getPath();
		$dirPath = dirname($path);
		if (!file_exists($dirPath)) {
			mkdir($dirPath, 0755, true);
		}
		return file_put_contents($path, $content);
	}
}