<?php
namespace Mu\Kernel\Script;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

abstract class Core extends Kernel\Core
{
    public function __realConstruct()
    {
        set_error_handler(array($this, 'exceptionsErrorHandler'));
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
                $row =  'x' . $jsonSerialize['id'] . ' job PROGRESS : ' . $jsonSerialize['processor'] . ', function : ' . $jsonSerialize['content']['function'] .
                    ' with parameters : ' . $this->getApp()->getToolbox()->arrayWithKeyToString($jsonSerialize['content']['parameters']);
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

    function exceptionsErrorHandler($severity, $message, $filename, $lineno)
    {
        if (error_reporting() == 0) {
            return;
        }
        if (error_reporting() & $severity) {
            throw new \ErrorException($message, 0, $severity, $filename, $lineno);
        }
    }
}
