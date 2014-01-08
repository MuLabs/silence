<?php
namespace Mu\Kernel\View\Twig;

use Mu\Kernel;

class Service extends Kernel\View\Service
{
	private $twig;
	protected $specificDir = 'twig';

	/**
	 * @return View
	 * @throws Kernel\View\Exception
	 */
	public function getView()
	{
		if (!$this->twig) {
			$this->initialize();
		}
		/** @var View $view */
		$view = parent::getView();
		$view->setTwig($this->twig);

		return $view;
	}

	private function initialize()
	{
		require_once VENDOR_PATH . '/twig/twig/lib/Twig/Autoloader.php';
		require_once VENDOR_PATH . '/twig/extensions/lib/Twig/Extensions/Autoloader.php';
		ini_set('unserialize_callback_func', 'spl_autoload_call');
		$this->getApp()->getToolbox()->registerAutoload(array('\\Twig_Autoloader', 'autoload'));
		$this->getApp()->getToolbox()->registerAutoload(array('\\Twig_Extensions_Autoloader', 'autoload'));

		$loader = new \Twig_Loader_Filesystem($this->getDir());
		$loader->addPath(KERNEL_PATH.'/template/twig', 'template');
		$this->twig = new \Twig_Environment($loader, array(
			'cache' => ($this->getCompileDir()) ? $this->getCompileDir() : false,
		));

		foreach ($this->getExtensions() as $name => $extension) {
			$this->twig->addExtension($this->getExtension($name));
		}
	}
}