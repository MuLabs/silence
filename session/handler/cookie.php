<?php
namespace Beable\Kernel\Session\Handler;

use Beable\Kernel;

/**
 * Cookie Handler ::
 * Manage cookies with contexts and use 2 protected keys to check validity of the user cookie
 *
 * Configuration:
 * [ses_cookie]
 * ; context = expire(in hour),secure(int),httponly(int)
 * murloc = 24,0,0
 *
 * @package Beable\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Cookie extends Kernel\Session\Handler
{
	private $keyVerify = 'mu_verify';
	private $keyTime   = 'mu_time';
	private $salt;
	private $expire;		// In hours
	private $secure;		// Bool
	private $httponly;		// Bool
	private $info;

	/**
	 * Load current cookie and check it's validity
	 */
	public function init()
	{
		// Initialize configuration:
		$this->salt   = $this->getConfig('salt', 'Z10uzuhyNyH3FYN9');
		$this->expire = $this->getConfig('expire', 24);
		$this->secure = $this->getConfig('secure', false);
		$this->httponly = $this->getConfig('httponly', false);

		// Load cookie if verifying key is valid:
		$this->info = array();
		if (isset($_COOKIE[$this->getContext()])) {
			// Test validity:
			if (!isset($_COOKIE[$this->getContext()][$this->keyVerify]) || $_COOKIE[$this->getContext()][$this->keyVerify]!=$this->getId()) {
				// TODO add log
				return;
			}
			// Test timestamp:
			if (!isset($_COOKIE[$this->getContext()][$this->keyTime]) || time()-$_COOKIE[$this->getContext()][$this->keyTime] > $this->expire*3600) {
				// TODO add log
				return;
			}

			// Store infos:
			$this->info = $_COOKIE[$this->getContext()];
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function save()
	{
		// Force the cookie to be cleaned if needed, but after rendering:
		if (count($this->info) == 0) {
			setcookie($this->getContext(), "");
		}
		else {
			// Set protected keys:
			$this->info[$this->keyVerify] = $this->getId();
			$this->info[$this->keyTime]   = time();

			// Save values:
			$expire = time() + $this->expire*3600;
			foreach ($this->info as $key=>$value) {
				setcookie($this->getContext().'['.$key.']', $value, $expire, '', '', $this->secure, $this->httponly);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function parseConfig(array $config = array())
	{
		return array(
			'expire' => (isset($config[0])) ? $config[0] : 12,
			'secure' => (isset($config[1]) && $config[1]==1),
			'httponly' => (isset($config[2]) && $config[2]==1)
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
		return md5($this->getContext().'--'.$this->salt);
	}

	/**
	 * {@inheritDoc}
	 */
	public function set($name, $value = null)
	{
		// Do not overload protected keys
		if ($name != $this->keyVerify || $name != $this->keyTime) {
			$this->info[$name] = $value;
		}
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
