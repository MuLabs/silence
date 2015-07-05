<?php
namespace Mu\Kernel\View\Volt;

use Mu\Kernel;

class Service extends Kernel\Service\Core {
    private $volt;
    public function __construct() {
        $this->volt = new \Phalcon\Mvc\View\Engine\Volt($this->getApp()->getViewManager()->getPhalconView(), $this->getApp()->getServicer());
    }

    public function __call($fct, $arguments) {
        return call_user_func_array(array($this->volt, $fct), $arguments);
    }
}
