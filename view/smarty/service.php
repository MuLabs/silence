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
		require_once(VENDOR_PATH . '/smarty/smarty/distribution/libs/Smarty.class.php');
		$this->smarty = new \Smarty();

		$this->smarty->setTemplateDir($this->getDir());
		$this->smarty->setCompileDir($this->getCompileDir());
		$this->smarty->registerFilter('pre', array($this, 'stripPrefilter'));
		$this->caching = 0;

		$this->getApp()->getToolbox()->registerAutoload('\\smartyAutoload');
	}

	public function stripPrefilter($source, \Smarty_Internal_Template $template)
	{
		$pos = strpos($source, '<script');
		if ($pos === false) {
			return str_replace(array("\r\n", "\r", "\n"), '', $source);
		}

		$subStr1 = substr($source, 0, $pos);
		$subStr2 = substr($source, $pos);

		return str_replace(array("\r\n", "\r", "\n"), '', $subStr1) . $subStr2;
	}
}