<?php
namespace Mu\Kernel\Communication;

use Mu\Kernel;

/**
 * Class Service
 * Should be instanciated into the Application as :
 *	$comS = new Kernel\Session\Service();
 *	$manager->register('com', $comS);
 *	$sessionS->addHandler('email', $comS->generateHandler('email', 'context'));
 *
 * @package Mu\Kernel\Session
 * @author Olivier Stahl
 */
class Service extends Kernel\Service\Extended
{
	const DEFAULT_HANDLER = 'email';

	/**
	 * @param $message
	 * @param $to
	 * @param $from
	 * @param $handler		Load an handler by its type
	 * @return void
	 * @throws \Exception
	 */
	public function send($message, $to, $from, $handler = self::DEFAULT_HANDLER)
	{
		try {
			// Try to get context handler:
			/** @var Handler $handler */
			$handler = $this->getHandler($handler, $handler);	// Load an handler with context name = handler type

			// Set handler infos:
			$handler->setContent($message);
			$handler->setDestination($to);
			$handler->setOrigin($from);

			// Fire send message:
			$handler->send();
		} catch (\Exception $e) {
			throw $e;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDirectory()
	{
		return 'communication';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNamespace()
	{
		return __NAMESPACE__;
	}
}
