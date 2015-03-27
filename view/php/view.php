<?php
namespace Mu\Kernel\View\Php;

use Mu\Kernel;

class View extends Kernel\View\View
{
	protected $extension = 'php';

	/**
	 * @inheritdoc
	 */
	public function render()
	{
		foreach ($this->getVars() as $key => $value) {
			$$key = $value;
		}

		include $this->getTemplateFilepath($this->template, $this->fragment);
		return ob_get_clean();
	}
}