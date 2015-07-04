<?php
namespace Mu\Kernel\Asset\Generator;

use Mu\Kernel;

class Fake extends Kernel\Asset\Generator
{
	/**
	 * @return int
	 */
	public function generateAsset()
	{
		$content = $this->minify($this->getFullContent()) . "\r\n";
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
		return $content;
	}
}