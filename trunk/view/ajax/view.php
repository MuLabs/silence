<?php
namespace Mu\Kernel\View\Ajax;

use Mu\Kernel;

class View extends Kernel\View\View
{
	/**
	 * @param string $target
	 * @param null|string $fragment
	 * @return string
	 */
	public function fetch($target, $fragment = null)
	{
        // Get the default view to build the HTML:
        $defaultView = $this->getApp()->getViewManager()->getView();
        $defaultView->setVars($this->getVars());

        // Initialize return values
        $aReturn = array(
            'html'      => $defaultView->fetch($target, $fragment),
            'messages'  => array(
                'error'     => $this->getVar('errorReport'),
                'warn'      => $this->getVar('warningReport'),
                'success'   => $this->getVar('successReport'),
                'info'      => $this->getVar('infoReport')
            )
        );

        // Return json encoded data:
        header('Content-type: application/json');
		return json_encode($aReturn);
	}
}