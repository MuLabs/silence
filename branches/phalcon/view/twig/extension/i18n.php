<?php
namespace Mu\Kernel\View\Twig\Extension;

use Mu\Kernel;

class i18n extends \Twig_Extensions_Extension_I18n
{
	use Kernel\View\Extension\i18n;

	/**
	 * @return array
	 */
	public function getFunctions()
	{
		$functions = parent::getFunctions();

		$functions['getCurrentLang'] = new \Twig_Function_Method($this, 'getCurrentLang');
		$functions['switchLang'] = new \Twig_Function_Method($this, 'switchLang');

		return $functions;
	}
}