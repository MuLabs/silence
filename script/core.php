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

    abstract function execute(array $params);

    function writeLine(array $param)
    {
        $date   = new \DateTime();
        $row    = '';

        switch($param['status']) {
            case Bundle\Queue\Model\Manager\QueueMessage::PROGRESS:
                /** @var Bundle\Queue\Model\Entity\QueueMessage $queueMessage */
                $queueMessage   = $param['queue'];
                $jsonSerialize  = $queueMessage->jsonSerialize();
                $row =  'x' . $jsonSerialize['id'] . ' job PROGRESS : ' . $jsonSerialize['processor'] . ', function : ' . $jsonSerialize['content']['function'];
                break;
            case Bundle\Queue\Model\Manager\QueueMessage::ERROR:
                $row = 'job ERROR : ' . $param['msg'];
                break;
            case Bundle\Queue\Model\Manager\QueueMessage::FATAL:
                $row = 'FATAL ERROR : ' . $param['msg'];
                break;
            case Bundle\Queue\Model\Manager\QueueMessage::SUCCESS:
                $row = 'job DONE';
                break;
            case 'wait4it':
                $row = 'A queue process is still in progress';
                break;
            case 'finish':
                $row = 'no more job in queue';
                break;
        }

        echo $date->format('Y/m/d H:i:s') . ' ' . $row, PHP_EOL;
    }

    function exceptionsFatalError()
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
