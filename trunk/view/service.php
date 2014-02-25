<?php
namespace Mu\Kernel\View;

use Mu\Kernel;

abstract class Service extends Kernel\Service\Core
{
	private $dir = '';
	private $compileDir = '';
	protected $specificDir = null;
	private $extensions = array();

	/**
	 * @param string $dir
	 */
	public function setDir($dir)
	{
		$this->dir = $dir . '/' . $this->specificDir;
	}

	/**
	 * @return string
	 */
	public function getDir()
	{
		return $this->dir;
	}

	/**
	 * @param string $compileDir
	 */
	public function setCompileDir($compileDir)
	{
		$this->compileDir = $compileDir . '/' . $this->specificDir;
	}

	/**
	 * @return string
	 */
	public function getCompileDir()
	{
		return $this->compileDir;
	}

	/**
	 * @param string $name Extension name
	 */
	public function addExtension($name)
	{
		$this->extensions[$name] = null;
	}

	/**
	 * @param $name
	 * @return Kernel\Core
	 */
	public function getExtension($name)
	{
		if (!isset($this->extensions[$name])) {
			$className = $this->getClassBaseName() . 'Extension\\' . $name;

			/** @var Kernel\Core $extension */
			$extension = new $className();
			$extension->setApp($this->getApp());
			$this->extensions[$name] = $extension;
		}

		return $this->extensions[$name];
	}

	/**
	 * @return Kernel\Core[]
	 */
	protected function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * @return View
	 * @throws Exception
	 */
	public function getView()
	{
		$classname = $this->getViewClassName();
		/** @var View $view */
		$view = new $classname();
		$view->setApp($this->getApp());
		$view->setService($this);

		// Initialize language if supported
		$localization = $this->getApp()->getLocalizationService();
		if ($localization) {
			$localization->getCurrentLanguage();
		}

		return $view;
	}

	/**
	 * @return string
	 */
	protected function getViewClassName()
	{
		return $this->getClassBaseName() . 'View';
	}

	/**
	 * @return string
	 */
	protected function getClassBaseName()
	{
		return str_replace('Service', '', get_called_class());
	}
}