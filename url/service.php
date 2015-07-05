<?php
namespace Mu\Kernel\Url;

use Mu\Kernel;

class Service extends Kernel\Service\Core {
    private $url;
    public function __construct() {
        $this->url = new \Phalcon\Mvc\Url();
    }

    public function __call($fct, $arguments) {
        return call_user_func_array(array($this->url, $fct), $arguments);
    }
}