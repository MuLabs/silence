<?php
namespace Mu\Kernel\Session\Handler;

use Mu\Kernel;

/**
 * Cookie Handler ::
 * Manage cookies with contexts and use 2 protected keys to check validity of the user cookie
 *
 * Configuration:
 * [ses_cookie]
 * ; context = expire(in hour),secure(int),httponly(int)
 * murloc = 24,0,0
 *
 * @package Mu\Kernel\Session\Handler
 * @author Olivier Stahl
 */
class Cookie extends Kernel\Session\Handler
{
	const DEFAULT_EXPIRE = 12;
	const DEFAULT_HTTPONLY = false;
	const DEFAULT_SALT = 'Z10uzuhyNyH3FYN9';
	const DEFAULT_SECURE = false;

	private $keyVerify = 'mu_verify';
	private $keyTime = 'mu_time';
	private $salt;
	private $expire; // In hours
	private $secure; // Bool
	private $httponly; // Bool
	private $info = array();

	/**
	 * Load current cookie and check it's validity
	 */
	public function __init()
	{
		// Initialize configuration:
		$this->salt = $this->getConfig('salt', self::DEFAULT_SALT);
		$this->expire = $this->getConfig('expire', self::DEFAULT_EXPIRE);
		$this->secure = $this->getConfig('secure', self::DEFAULT_SECURE);
		$this->httponly = $this->getConfig('httponly', self::DEFAULT_HTTPONLY);

		// Get cookie:
		$cookie = $this->__getCookie();
		foreach ($cookie as $key => $jsonValue) {
			$cookie[$key] = json_decode($jsonValue, true);
		}

		// Test cookie validity if not empty
		if (is_array($cookie)) {
			// Test validity:
			if (!isset($cookie[$this->keyVerify]) || $cookie[$this->keyVerify] != $this->getId()) {
				return;
			}
			// Test timestamp:
			if (!isset($cookie[$this->keyTime]) || time() - $cookie[$this->keyTime] > $this->expire * 3600) {
				return;
			}

			// Store infos:
			$this->info = $cookie;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function __close()
	{
		$this->save();
	}

	/**
	 * {@inheritDoc}
	 */
	public function save()
	{
		// Test if header has been already sent, in this case cookie couldn't be saved:
		if (headers_sent()) {
			return; // TODO Add log
		}

		// Force the cookie to be cleaned if needed, but after rendering:
		if (count($this->info) == 0) {
			foreach ($this->__getCookie() as $key => $value) {
				setcookie($this->getContext() . '[' . $key . ']', null, -1);
			}
		} else {
			// Set protected keys:
			$this->info[$this->keyVerify] = $this->getId();
			$this->info[$this->keyTime] = time();

			// Save values:
			$expire = time() + $this->expire * 3600;
			foreach ($this->info as $key => $value) {
				setcookie(
					$this->getContext() . '[' . $key . ']',
					json_encode($value),
					$expire,
					'/',
					'',
					$this->secure,
					$this->httponly
				);
			}
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function parseConfig(array $config = array())
	{
		return array(
			'expire' => (isset($config[0])) ? $config[0] : self::DEFAULT_EXPIRE,
			'secure' => (isset($config[1]) && $config[1] == 1),
			'httponly' => (isset($config[2]) && $config[2] == 1)
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
		return md5($this->getContext() . '--' . $this->salt);
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

	/**
	 * Get correct cookie
	 * @return array
	 */
	private function __getCookie()
	{
		return $this->getApp()->getHttp()->getRequest()->getParameters(
			$this->getContext(),
			Kernel\Http\Request::PARAM_TYPE_COOKIE,
			array()
		);
	}
}
