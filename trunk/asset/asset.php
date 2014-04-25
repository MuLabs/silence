<?php
namespace Mu\Kernel\Asset;

use Mu\Kernel;

class Asset extends Kernel\Core
{
	protected $key;
	protected $ext;
	protected $fileList;
	protected $manager;
	protected $fileTime;

	public function __construct(Service $manager, $fileList)
	{
		if (!count($fileList)) {
			throw new Exception('', Exception::ASSET_EMPTY);
		}

		$file = reset($fileList);
		$defaultExt = pathinfo($file, PATHINFO_EXTENSION);
		if (!$manager->isExtensionAllowed($defaultExt)) {
			throw new Exception($defaultExt . ' - ' . $file, Exception::INVALID_EXTENSION);
		}

		foreach ($fileList as $file) {
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			if ($ext !== $defaultExt) {
				throw new Exception($ext . ' - ' . $file, Exception::INVALID_EXTENSION);
			}
		}

		$this->fileList = $fileList;
		$this->key = $this->generateKey();
		$this->ext = $defaultExt;
		$this->manager = $manager;
		$this->setApp($manager->getApp());
	}

	/**
	 * @return Service
	 */
	public function getManager()
	{
		return $this->manager;
	}

	/**
	 * @return string
	 */
	private function generateKey()
	{
		$list = $this->getFileList();
		sort($list);
		return sha1(json_encode($list));
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return APP_STATIC_PATH . '/' . Service::ASSET_DIR . '/' . $this->getKey() . '.' . $this->getManager(
		)->getGenerator($this)->getOutExt();
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getExt()
	{
		return $this->ext;
	}

	/**
	 * @return array
	 */
	public function getFileList()
	{
		return $this->fileList;
	}

	public function generate()
	{
		$this->getManager()->getGenerator($this)->generateAsset();
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->getApp()->getUrlStatic(
			Service::ASSET_DIR . '/' . $this->getKey() . '.' . $this->getManager()->getGenerator($this)->getOutExt()
		) . '?v=' . $this->getFiletime();
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		return $this->getManager()->getGenerator($this)->getHtml();
	}

	/**
	 * @return bool
	 */
	public function exists()
	{
		return file_exists($this->getPath());
	}

	/**
	 * @return int
	 */
	public function getFiletime()
	{
		if ($this->fileTime === null) {
			$this->fileTime = filemtime($this->getPath());
		}
		return $this->fileTime;
	}
}