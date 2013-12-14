<?php
namespace Beable\Kernel\View;

use Beable\Kernel;

abstract class View extends Kernel\Core
{
	private $service;
	private $compileDir;
	private $vars = array();

	public function __construct()
	{

	}

	/**
	 * @return string
	 */
	public function getDir()
	{
		return $this->getService()->getDir();
	}

	/**
	 * @return string
	 */
	public function getCompileDir()
	{
		return $this->getService()->getCompileDir();
	}

	/**
	 * @param Service $service
	 */
	public function setService(Service $service)
	{
		$this->service = $service;
	}

	/**
	 * @return Service
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @param string $var
	 * @param mixed $value
	 */
	public function setVar($var, $value)
	{
		$this->vars[$var] = $value;
	}

	/**
	 * @param array $vars
	 */
	public function setVars(array $vars)
	{
		$this->vars = array_merge($this->vars, $vars);
	}

	/**
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	public function getVar($var, $default = null)
	{
		if (isset($this->vars[$var])) {
			return $this->vars[$var];
		} else {
			return $default;
		}
	}

	/**
	 * @return array
	 */
	public function getVars()
	{
		return $this->vars;
	}

	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	abstract public function fetch($target, $fragment = null);

	/**
	 * @param string $target
	 * @throws Exception
	 */
	protected function isValid($target)
	{
		if (!file_exists($this->getDir())) {
			throw new Exception($this->getDir(), Exception::DIR_NOT_FOUND);
		}

		if ($this->compileDir && !file_exists($this->getCompileDir())) {
			throw new Exception($this->getCompileDir(), Exception::COMPILE_DIR_NOT_FOUND);
		}

		if (!file_exists($this->getTargetFilepath($target))) {
			throw new Exception($this->getTargetFilepath($target), Exception::TARGET_NOT_FOUND);
		}
	}

	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	protected function getTargetFilepath($target, $fragment = null)
	{
		if ($fragment === null) {
			return $this->getDir() . '/' . $target . '.php';
		}
		return $this->getDir() . '/fragment/' . $target . '/' . $fragment . '.php';
	}

	/**
	 * @param string $name
	 * @return \Twig_extension
	 */
	public function getExtension($name)
	{
		return $this->getService()->getExtension($name);
	}
}