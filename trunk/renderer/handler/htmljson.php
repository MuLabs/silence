<?php
namespace Mu\Kernel\Renderer\Handler;

use Mu\Kernel;

class HtmlJson extends Kernel\Renderer\Handler
{
	/**
	 * @inheritdoc
	 */
	public function render(Kernel\View\View $view)
	{
        // Initialize return values
        $aReturn = array(
            'html'      => $view->render(),
            'messages'  => array(
                'error'     => $view->getVar('errorReport'),
                'warn'      => $view->getVar('warningReport'),
                'success'   => $view->getVar('successReport'),
                'info'      => $view->getVar('infoReport')
            )
        );

        // Get redirection link:
        if ($view->getVar('redirect', false)) {
            $aReturn['redirect'] = $view->getVar('redirect');
        }

        // Return json encoded data:
		return json_encode($aReturn);
	}

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'application/json';
    }
}