<?php
namespace Mu\Kernel\View\Smarty;

use Mu\Kernel;

class Service extends Kernel\View\Service
{
	protected $smarty;
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
			$this->initializeSmarty();
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

	private function initializeSmarty()
	{
		define('SMARTY_SPL_AUTOLOAD', 0);
		require_once(VENDOR_PATH . '/smarty/smarty/distribution/libs/Smarty.class.php');
		$this->getApp()->getToolbox()->registerAutoload('smartyAutoload');
		$this->smarty = new \Smarty();
		$this->addDir(KERNEL_PATH . '/backoffice/view/');

		$dirsList = array_reverse($this->getDir());
		foreach ($dirsList as $oneDir) {
			$this->smarty->addTemplateDir($oneDir . '/' . $this->getSpecificDir());
		}

		$this->smarty->setCompileDir($this->getCompileDir());
		$this->smarty->registerFilter('pre', array($this, 'stripPrefilter'));
		$this->caching = 0;
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