<?php
namespace Mu\Kernel\View\Twig\Extension;

use Mu\Kernel;

class Core extends \Twig_Extension
{
	use Kernel\View\Extension\Core;

	/**
	 * @return array
	 */
	public function getFunctions()
	{
		return array(
			'asset' 		=> new \Twig_Function_Method($this, 'asset', array('is_safe' => array('html'))),
			'getUrl' 		=> new \Twig_Function_Method($this, 'getUrl'),
			'getConstant'	=> new \Twig_Function_Method($this, 'getConstant'),
			'getUrlStatic' 	=> new \Twig_Function_Method($this, 'getUrlStatic'),
			'thisUrl' 		=> new \Twig_Function_Method($this, 'thisUrl'),
			'addFragment' 	=> new \Twig_Function_Method($this, 'addFragment', array('is_safe' => array('html'))),
			'thisFragment' 	=> new \Twig_Function_Method($this, 'thisFragment', array('is_safe' => array('html'))),
			'strftime' 		=> new \Twig_Filter_Function('strftime'),
		);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return get_called_class();
	}
}