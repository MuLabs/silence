<?php
namespace Mu\Kernel\Tag;

use Mu\Kernel;

class Service extends Kernel\Service\Core {
    private $tag;
    public function __construct() {
        $this->tag = new \Phalcon\Tag();
    }

    public function __call($fct, $arguments) {
        return call_user_func_array(array($this->tag, $fct), $arguments);
    }
}