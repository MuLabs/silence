<?php
namespace Mu\Kernel\Renderer\Handler;

use Mu\Kernel;

class Html extends Kernel\Renderer\Handler
{
	/**
	 * @inheritdoc
	 */
	public function render(Kernel\View\View $view)
	{
		return $view->render();
	}

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'text/html';
    }
}