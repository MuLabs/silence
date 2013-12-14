<?php
namespace Beable\Kernel\View\Php;

use Beable\Kernel;

class View extends Kernel\View\View
{
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

		require_once $this->getTargetFilepath($target, $fragment);
		$content = ob_get_contents();
		ob_clean();
		return $content;
	}
}