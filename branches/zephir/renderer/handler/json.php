<?php
namespace Mu\Kernel\Renderer\Handler;

use Mu\Kernel;

class Json extends Kernel\Renderer\Handler
{
    /**
     * @inheritdoc
     */
	public function render(Kernel\View\View $view)
	{
		return json_encode($view->getSafeVars());
	}

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'application/json';
    }
}