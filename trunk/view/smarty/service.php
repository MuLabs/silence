<?php
namespace Mu\Kernel\View\Smarty;

use Mu\Kernel;

class Service extends Kernel\View\Service
{
	private $smarty;
	protected $specificDir = 'smarty';

	/**
	 * @param $smarty
	 */
	public function setSmarty(\Smarty $smarty)
	{
		$this->smarty = $smarty;
	}

	/**
	 * @return \Smarty
	 */
	public function getSmarty()
	{
		if (!$this->smarty) {
			$this->initialize();
		}

		return $this->smarty;
	}

	/**
	 * @return View
	 * @throws Kernel\View\Exception
	 */
	public function getView()
	{
		/** @var View $view */
		$view = parent::getView();
		$view->setSmarty($this->getSmarty());

		return $view;
	}

	private function initialize()
	{
		require_once(VENDOR_PATH . '/smarty/smarty/distribution/libs/Smarty.class.php');
		$this->smarty = new \Smarty();

		$this->smarty->setTemplateDir($this->getDir());
		$this->smarty->setCompileDir($this->getCompileDir());
		//$this->smarty->registerFilter('pre', array($this, 'stripPrefilter'));
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