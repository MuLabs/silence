<?php
namespace Beable\Kernel\Asset\Generator;

use Beable\Kernel;

class Js extends Kernel\Asset\Generator
{
	/**
	 * @return int
	 */
	public function generateAsset()
	{
		$content = $this->minify($this->getFullContent());
		$path = $this->getAsset()->getPath();
		$dirPath = dirname($path);
		if (!file_exists($dirPath)) {
			mkdir($dirPath, 0755, true);
		}
		return file_put_contents($path, $content);
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		return '<script type="text/javascript" src="' . $this->getAsset()->getUrl() . '"></script>';
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function minify($content)
	{
		require_once(KERNEL_LIBS_PATH . '/Minify/Minify_JS_ClosureCompiler.php');
		return \Minify_JS_ClosureCompiler::minify($content);
	}
}