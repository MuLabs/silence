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
        $vars = $view->getSafeVars();
        foreach ($vars as $key => $oneVar) {
            if ($oneVar instanceof Kernel\Core) {
                $vars[$key]->setApp(null);
            }
        }
		return json_encode($vars);
	}

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'application/json';
    }
}