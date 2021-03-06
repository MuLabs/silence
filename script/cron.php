<?php
namespace Mu\Kernel\Script;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

abstract class Cron extends Core
{
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
        parent::__destruct();
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
}
