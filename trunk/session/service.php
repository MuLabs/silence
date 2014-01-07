<?php
namespace Mu\Kernel\Session;

use Mu\Kernel;

/**
 * Class Service
 * Should be instanciated into the Application as :
 *	$sessionS = new Kernel\Session\Service();
 *	$manager->register('session', $sessionS);
 *	$sessionS->addHandler('session', $sessionS->generateHandler('cookie'));
 *
 * @package Mu\Kernel\Session
 * @author Olivier Stahl
 */
class Service extends Kernel\Service\Extended
{
	/**
	 * {@inheritdoc}
	 */
	protected function getDirectory()
	{
		return 'session';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNamespace()
	{
		return __NAMESPACE__;
	}

	/**
	 * @param string $type
	 * @param string $context
	 * @return Kernel\Session\Handler
	 */
	public function getHandler($type, $context = Kernel\Handler\Core::DEFAULT_CONTEXT) {
		return parent::getHandler($type, $context);
	}
}
