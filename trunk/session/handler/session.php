<?php
namespace Beable\Kernel\Session\Handler;

use Beable\Kernel;

/**
 * Session Handler ::
 * Manage session with contexts
 * Manage the session_start and write_close
 *
 * Configuration: no configuration
 *
 * @package Beable\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Session extends Kernel\Session\Handler
{
	private static $session_number = 0;

	/**
	 * Start php session if needed and set context
	 * @param string $context
	 */
	public function init($context = '')
	{
	 self::$session_number++;
	 if (session_status() !== PHP_SESSION_ACTIVE) {
	  session_start();
	 }
	}

	/**
	 * {@inheritDoc}
	 */
	public function close()
	{
	 self::$session_number--;
	 if (self::$session_number <= 0 && session_status() === PHP_SESSION_ACTIVE) {
	  session_write_close();
	 }
	}

	/**
	 * {@inheritDoc}
	 */
	public function clean()
	{
	 $this->setAll();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($name, $default = null)
	{
	 return (isset($_SESSION[$this->getContext()][$name])) ? $_SESSION[$this->getContext()][$name] : $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAll()
	{
	 return (isset($_SESSION[$this->getContext()])) ? $_SESSION[$this->getContext()] : array();
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
	 $_SESSION[$this->getContext()][$name] = $value;
	 return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAll($values = array())
	{
	 $_SESSION[$this->getContext()] = $values;
	}
}
