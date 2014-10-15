<?php
namespace Mu\Kernel\Script;

use Mu\Kernel;
use Mu\App;
use Mu\Bundle;

abstract class Core extends Kernel\Core
{
    public function __construct()
    {
        $this->log('Start script');
    }

    public function __realConstruct()
    {
        register_shutdown_function(array($this, 'exceptionsFatalError'));

        $siteService = $this->getApp()->getSiteService();
        $siteService->loadSiteUrl();
        $siteList = $siteService->getSites();
        $site = reset($siteList);
        $siteService->setCurrentSite($site);
    }

    public function __destruct()
    {
        $this->log('End script');
    }

    abstract public function execute(array $params);

    protected function getStatusMessage($param)
    {
        return '';
    }

    public function writeStatus(array $param)
    {
        $this->log($this->getStatusMessage($param));
    }

    public function ask($message)
    {
        $this->log($message);
        return trim(fgets(STDIN));
    }

    public function log($message)
    {
        $date = new \DateTime();
        error_log($date->format('Y/m/d H:i:s') . ' ' . $message);
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
