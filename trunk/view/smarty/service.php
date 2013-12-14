<?php
namespace Beable\Kernel\View\Smarty;

use Beable\Kernel;

class Service extends Kernel\View\Service
{
	private $smarty;
	protected $specificDir = 'smarty';

	/**
	 * @return View
	 * @throws Kernel\View\Exception
	 */
	public function getView()
	{
		if (!$this->smarty) {
			$this->initialize();
		}
		/** @var View $view */
		$view = parent::getView();
		$view->setSmarty($this->smarty);

		return $view;
	}

	private function initialize()
	{
		require_once(KERNEL_LIBS_PATH . '/Smarty/Smarty.class.php');
		$this->smarty = new \Smarty();

		$this->smarty->setTemplateDir($this->getDir());
		$this->smarty->setCompileDir($this->getCompileDir());
		$this->caching = 0;

		$this->getApp()->getToolbox()->registerAutoload('\\smartyAutoload');
	}
}