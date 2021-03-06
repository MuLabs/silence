<?php
namespace Mu\Kernel\Asset;

use Mu\Kernel;

abstract class Generator extends Kernel\Core
{
	protected $asset;

	public function __construct(Asset $asset)
	{
		$this->asset = $asset;
	}

	/**
	 * @return Asset
	 */
	public function getAsset()
	{
		return $this->asset;
	}

	/**
	 * @throws Exception
	 * @return string
	 */
	public function getFullContent()
	{
		$content = '';
		foreach ($this->getAsset()->getFileList() as $file) {
			$content .= file_get_contents(APP_STATIC_PATH . '/' . $file);

			if (!$content) {
				throw new Exception($file, Exception::FILE_NOT_FOUND);
			}
		}

		return $content;
	}

	abstract public function generateAsset();

	abstract public function getHtml();

	/**
	 * @return string
	 */
	public function getOutExt()
	{
		return $this->getAsset()->getExt();
	}
}