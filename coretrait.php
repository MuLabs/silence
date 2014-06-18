<?php
namespace Mu\Kernel;

use Mu\App;
use Mu\Kernel;

trait CoreTrait
{
    protected $application;
    protected $logger;

    /**
     * @param Kernel\Application $app
     */
    public function setApp(Application $app = null)
    {
        $this->application = $app;
    }

    /**
     * @return App\Application
     */
    public function getApp()
    {
        return $this->application;
    }

    /**
     * @return Kernel\Log\Service
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Kernel\Log\Service $logger
     */
    public function setLogger(Kernel\Log\Service $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $section
     * @param mixed $log
     * @throws Exception
     */
    public function log($section, $log)
    {
        if (!$this->getLogger()) {
            throw new Exception(get_called_class(), Exception::NO_LOGGER);
        }

        if (is_array($log)) {
            $log = print_r($log, true);
        }

        $this->getLogger()->log($section, $log);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getConstant($name)
    {
        return constant('static::' . $name);
    }

    /**
     * @param string $trait
     * @return bool
     */
    public function hasTrait($trait)
    {
        if (!is_string($trait)) {
            return false;
        }
        return property_exists($this, '__' . $trait);
    }
}