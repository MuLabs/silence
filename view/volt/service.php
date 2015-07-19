<?php
namespace Mu\Kernel\View\Volt;

use Mu\Kernel;

class Service extends Kernel\Service\Core {
    private $volt;
    public function initialize() {
        $this->volt = new \Phalcon\Mvc\View\Engine\Volt($this->getApp()->getViewService()->getPhalconView(), $this->getApp()->getServicer());
        $this->volt->setDI($this->getApp()->getDI());
    }

    public function __call($fct, $arguments) {
        return call_user_func_array(array($this->volt, $fct), $arguments);
    }
}
