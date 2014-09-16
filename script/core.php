<?php
namespace Mu\Kernel\Script;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

abstract class Core extends Kernel\Core
{
    public function __realConstruct()
    {
        register_shutdown_function(array($this, 'exceptionsFatalError'));
    }

    public function __destruct() {}

    abstract public function execute(array $params);

    protected function getStatusMessage($param)
    {
        return '';
    }

    public function writeLine(array $param)
    {
        $date = new \DateTime();
        $row = $this->getStatusMessage($param);

        if ($row) {
            echo $date->format('Y/m/d H:i:s') . ' ' . $row, PHP_EOL;
        }
    }

    public function exceptionsFatalError()
    {
        if ($error = error_get_last()) {
            switch ($error['type']) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            }
        }
        return;
    }
}
