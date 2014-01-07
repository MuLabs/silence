<?php
namespace Mu\Kernel\Log;

use Mu\Kernel;

/**
 * Class Service
 * @package Mu\Kernel\Log
 * @author Romain Schiano
 */
class Service extends Kernel\File\Service
{
	const DEFAULT_HANDLER = 'file';

	private $currentLog;

	/**
	 * @param string $section
	 * @param mixed $log
	 */
	public function log($section, $log)
	{
		if (!$this->getCurrentLog()) {
			$this->setCurrentLog($this->getHandler('text'));
		}

		if (!file_exists(LOG_PATH)) {
			mkdir(LOG_PATH, 0777, true);
		}

		$currentLog = $this->getCurrentLog();
		$currentLog->add(date('Y-m-d H:i:s') . ' - [' . $section . '] - ' . $log);
		$currentLog->append(LOG_PATH . '/' . date('Ymd') . '.log');
	}

	/**
	 * @param Kernel\File\Handler $handler
	 */
	public function setCurrentLog(Kernel\File\Handler $handler)
	{
		$this->currentLog = $handler;
	}

	/**
	 * @return Kernel\File\Handler
	 */
	public function getCurrentLog()
	{
		return $this->currentLog;
	}
}
