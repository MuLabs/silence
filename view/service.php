<?php
namespace Mu\Kernel\View;

use Mu\Kernel;
class Service extends Kernel\Service\Core {
    private $view;

    public function initialize() {
        $this->view = new \Phalcon\Mvc\View();
        $this->view->setDI($this->getApp()->getDI());
    }

    /**
     * @return \Phalcon\Mvc\View
     */
    public function getPhalconView() {
        return $this->view;
    }

    public function __call($fct, $arguments) {
        return call_user_func_array(array($this->view, $fct), $arguments);
    }
}
