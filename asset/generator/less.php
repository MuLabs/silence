<?php
namespace Mu\Kernel\Asset\Generator;

use Mu\Kernel;

class Less extends Kernel\Asset\Generator\Css
{
	protected $lesser;

	/**
	 * @return int
	 */
	public function generateAsset()
	{
		if (!isset($this->lesser)) {
			require_once VENDOR_PATH . '/leafo/lessphp/lessc.inc.php';
			$this->lesser = new \lessc();
		}


		$content = $this->lesser->compile($this->dumpVars() . "\n" . $this->getFullContent());
		$content = $this->minify($content);
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
	public function dumpVars()
	{
		$content = '';
		$vars = $this->getAsset()->getManager()->getVars();

		foreach ($vars as $key => $value) {
			$content .= '@' . $key . ' = \'' . $value . '\'; ';
		}

		return $content;
	}

	/**
	 * @return string
	 */
	public function getOutExt()
	{
		return 'css';
	}
}