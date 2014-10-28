<?php
namespace Mu\Kernel\Script;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

abstract class Cron extends Core
{
    private $standalone = true;

    public function __construct($standalone = true)
    {
        $this->standalone = $standalone;
        if ($this->standalone) {
            parent::__construct();
        }
    }

    public function __realConstruct()
    {
        parent::__realConstruct();

        if (!$this->getLock()) {
            $param = array('status' => 'wait4it');
            $this->writeStatus($param);
            exit;
        }
    }

    protected function getStatusMessage(array $param)
    {
        switch ($param['status']) {
            case 'wait4it':
                return 'A queue process is still in progress';
                break;
            default:
                return parent::getStatusMessage($param);
        }
    }

    public function __destruct()
    {
        if ($this->standalone) {
            parent::__destruct();
        }

        $this->releaseLock();
    }

    private function getLockName()
    {
        return $this->getApp()->getProjectName() . '|' . substr(get_called_class(), strrpos(get_called_class(), '/'));
    }

    public function getLock()
    {
        $nameLock = $this->getLockName();
        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');

        $sql = 'SELECT GET_LOCK("' . $nameLock . '", 1);';
        $result = $handler->query($sql);
        return $result->fetchValue();
    }

    public function releaseLock()
    {
        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');

        $sql = 'SELECT RELEASE_LOCK("' . $this->getLockName() . '");';
        $result = $handler->query($sql);
        return $result->fetchValue();
    }

    public function exceptionsFatalError()
    {
        $this->releaseLock();
        parent::exceptionsFatalError();
    }

    public function log($message)
    {
        if ($this->standalone) {
            parent::log($message);
        }
    }
}
