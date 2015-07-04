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
            'isInstanceof'	=> new \Twig_Function_Method($this, 'isInstanceof'),
			'asset' 		=> new \Twig_Function_Method($this, 'asset', array('is_safe' => array('html'))),
			'getUrl' 		=> new \Twig_Function_Method($this, 'getUrl'),
			'getUrlBase64' 	=> new \Twig_Function_Method($this, 'getUrlBase64'),
			'getConstant'	=> new \Twig_Function_Method($this, 'getConstant'),
			'getUrlStatic' 	=> new \Twig_Function_Method($this, 'getUrlStatic'),
			'getUrlSite' 	=> new \Twig_Function_Method($this, 'getUrlSite'),
			'thisUrl' 		=> new \Twig_Function_Method($this, 'thisUrl'),
			'addFragment' 	=> new \Twig_Function_Method($this, 'addFragment', array('is_safe' => array('html'))),
			'thisFragment' 	=> new \Twig_Function_Method($this, 'thisFragment', array('is_safe' => array('html'))),
			'getLoc' 		=> new \Twig_Function_Method($this, 'getLoc'),
			'getConvertedDate'	=> new \Twig_Function_Method($this, 'getConvertedDate'),
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