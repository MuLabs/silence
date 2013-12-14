<?php
namespace Beable\Kernel\Session;

use Beable\App\Application;
use Beable\Kernel;

/**
 * Class Service
 * Should be instanciated into the Application as :
 *	$sessionS = new Kernel\Session\Service();
 *	$manager->register('session', $sessionS);
 *	$sessionS->addHandler('session', $sessionS->generateHandler('cookie'));
 *
 * @package Beable\Kernel\Session
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
		return '\\Beable\\Kernel\\Session';
	}
}
