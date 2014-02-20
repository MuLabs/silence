<?php
namespace Mu\Kernel\View\Php;

use Mu\Kernel;

class View extends Kernel\View\View
{
	protected $extension = 'php';
	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function fetch($target, $fragment = null)
	{
		$this->isValid($target);

		foreach ($this->getVars() as $var => $value) {
			$$var = $value;
		}

		require_once $this->getTemplateFilepath($target, $fragment);
		$content = ob_get_contents();
		ob_clean();
		return $content;
	}
}