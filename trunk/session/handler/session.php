<?php
namespace Mu\Kernel\Session\Handler;

use Mu\Kernel;

/**
 * Session Handler ::
 * Manage session with contexts
 * Manage the session_start and write_close
 *
 * Configuration: no configuration
 *
 * @package Mu\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Session extends Kernel\Session\Handler
{
	private static $sessionNumber = 0;
	private $info = array();

	/**
	 * Start php session if needed and set context
	 * @param string $context
	 */
	public function __init($context = '')
	{
		self::$sessionNumber++;
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		// Get session value:
		$this->info = $this->getApp()->getHttp()->getRequest()->getParameters(
			$this->getContext(),
			Kernel\Http\Request::PARAM_TYPE_SESSION,
			array()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __close()
	{
		// Store infos into session:
		$this->save();

		// Close session if needed:
		self::$sessionNumber--;
		if (self::$sessionNumber <= 0 && session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save()
	{
		// Store infos into session:
		$this->getApp()->getHttp()->getRequest()->setParameter(
			$this->getContext(),
			Kernel\Http\Request::PARAM_TYPE_SESSION,
			$this->getAll()
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($name, $default = null)
	{
		return (isset($this->info[$name])) ? $this->info[$name] : $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAll()
	{
		return $this->info;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getId()
	{
		return session_id();
	}

	/**
	 * {@inheritDoc}
	 */
	public function set($name, $value = null)
	{
		$this->info[$name] = $value;
		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAll($values = array())
	{
		$this->info = $values;
	}
}
