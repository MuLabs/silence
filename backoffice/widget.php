<?php
namespace Mu\Kernel\Backoffice;

use Mu\Kernel;

abstract class Widget extends Kernel\Core
{
	protected $title;
	protected $defaultOptions = array();

	#region Getters
	/**
	 * @return string
	 */
	public function getName()
	{
		return substr(get_called_class(), strrpos(get_called_class(), '\\'));
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getDefaultOptions()
	{
		return $this->defaultOptions;
	}

	#endregion

	#region Setters
	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @param array $defaultOptions
	 */
	public function setDefaultOptions($defaultOptions)
	{
		$this->defaultOptions = $defaultOptions;
	}

	#endregion
}