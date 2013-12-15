<?php
namespace Beable\Kernel\Handler;

use Beable\Kernel;

abstract class Core extends Kernel\Core
{
	const DEFAULT_CONTEXT = 'murloc';

	private $context;
	private $config;
	protected $configPrefix = '';

	/**
	 * Initialize the handler
	 */
	abstract public function init();

	/**
	 * Correctly save the handler
	 */
	abstract public function save();

	/**
	 * Get a configuration value
	 * @param $name
	 * @param null $default
	 * @return null
	 */
	protected function getConfig($name, $default = null)
	{
		if (!isset($this->config)) {
			$this->loadConfig();
		}

		return (isset($this->config[$name])) ? $this->config[$name] : $default;
	}

	/**
	 * Load handler context configuration
	 */
	protected function loadConfig()
	{
		$configManager = $this->getApp()->getConfigManager();
		$configs = $configManager->get($this->configPrefix.$this->getClassName(), array());

		foreach ($configs as $context => $config) {
			if ($context == $this->getContext()) {
				$this->config = $this->parseConfig(explode(',', $config));
				return;
			}
		}

		// Load default:
		$this->config = array();
	}

	/**
	 * Format the configuration array to return an associative array
	 * @param array $config
	 * @return array
	 */
	protected function parseConfig(array $config = array())
	{
		return $config;
	}

	/**
	 * Get context of the handler
	 * @return mixed
	 */
	public function getContext()
	{
		// Set default if not set:
		if (!isset($this->context)) {
			$this->setContext(self::DEFAULT_CONTEXT);
		}

		// Return context:
		return $this->context;
	}
	public function setContext($context = self::DEFAULT_CONTEXT)
	{
		if (!isset($this->context) && is_string($context) && $context != '') {
			$this->context = $context;
		}
	}
}