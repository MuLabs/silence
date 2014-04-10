<?php
namespace Mu\Kernel\View\Twig;

use Mu\Kernel;

class Service extends Kernel\View\Service
{
	protected $twig;
	protected $specificDir = 'twig';

	/**
	 * @return \Twig_Environment
	 */
	public function getTwig()
	{
		if (!$this->twig) {
			$this->initializeTwig();
		}

		return $this->twig;
	}

	/**
	 * @return View
	 * @throws Kernel\View\Exception
	 */
	public function getView()
	{
		/** @var View $view */
		$view = parent::getView();
		$view->setTwig($this->getTwig());

		return $view;
	}

	private function initializeTwig()
	{
		require_once VENDOR_PATH . '/twig/twig/lib/Twig/Autoloader.php';
		require_once VENDOR_PATH . '/twig/extensions/lib/Twig/Extensions/Autoloader.php';
		ini_set('unserialize_callback_func', 'spl_autoload_call');
		$this->getApp()->getToolbox()->registerAutoload(array('\\Twig_Autoloader', 'autoload'));
		$this->getApp()->getToolbox()->registerAutoload(array('\\Twig_Extensions_Autoloader', 'autoload'));
		$this->addDir(KERNEL_PATH . '/template/twig', 'template');

		$dirList = $this->getDir();
		$loader = null;

		foreach ($dirList as $namespace => $oneDir) {
			if ($loader === null) {
				$loader = new \Twig_Loader_Filesystem($oneDir . '/' . $this->getSpecificDir());
			}
			$loader->addPath($oneDir, $namespace);
		}

		$this->twig = new \Twig_Environment($loader, array(
			'cache' => ($this->getCompileDir()) ? $this->getCompileDir() : false,
		));

		foreach ($this->getExtensions() as $name => $extension) {
			$this->twig->addExtension($this->getExtension($name));
		}
	}
}