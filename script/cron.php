<?php
namespace Mu\Kernel\Script;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

abstract class Cron extends Core
{
    public function __realConstruct() {
        parent::__realConstruct();

        if (!$this->getLock()) {
            $param = array('status'=>'wait4it');
            $this->writeLine($param);
            exit;
        }
    }

    public function __destruct() {
        parent::__destruct();

        $this->releaseLock();
    }

    public function getLock()
    {
        $nameLock = 'a';
        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');

        $sql   = 'SELECT IS_FREE_LOCK("'.$nameLock.'");';
        $result = $handler->query($sql);

        if (!$result->fetchValue()) {
            return false;
        }

        $sql   = 'SELECT GET_LOCK("'.$nameLock.'", 0);';
        $result = $handler->query($sql);
        return $result->fetchValue();
    }

    public function releaseLock()
    {
        $nameLock = 'a';
        $handler = $this->getApp()->getDatabase()->getHandler('writeFront');

        $sql   = 'SELECT RELEASE_LOCK("'.$nameLock.'");';
        $result = $handler->query($sql);
        return $result->fetchValue();
    }

    function exceptionsErrorHandler($severity, $message, $filename, $lineno)
    {
        $this->releaseLock();
        parent::exceptionsErrorHandler($severity, $message, $filename, $lineno);
    }
}
