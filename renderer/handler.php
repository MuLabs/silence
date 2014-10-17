<?php
namespace Mu\Kernel\Renderer;

use Mu\Kernel;

abstract class Handler extends Kernel\Core
{
    /**
     * @param Kernel\View\View $view
     * @retrun $string
     */
    abstract function render(Kernel\View\View $view);

    /**
     * @return string
     */
    abstract function getContentType();

    /**
     * @return string
     */
    public function getName()
    {
        return strtolower(get_class($this));
    }
}