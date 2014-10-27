<?php
namespace Mu\Kernel\Bundle;

use Mu\Kernel;

class Bundler extends Kernel\Core
{
	/** @var Core[] */
	protected $bundlesInstance = array();
    protected $bundles = array();

    /**
     * @param $name
     * @param $bundle
     */
    public function register($name, $bundle)
    {
        if (isset($this->bundlesInstance[$name])) {
            unset($this->bundlesInstance[$name]);
        }

        if (is_string($bundle)) {
            $this->bundles[$name] = $bundle;
        }
    }

    /**
     * @param $name
     * @throws Exception
     * @return Core
     */
    public function get($name)
    {
        if (!isset($this->bundlesInstance[$name])) {
            if (!isset($this->bundles[$name])) {
                throw new Exception($name, Exception::NOT_FOUND);
            } else {
                $this->set($name);
            }
        }

        return $this->bundlesInstance[$name];
    }

    /**
     * @param $name
     * @param Kernel\Bundle\Core $bundle
     * @throws Exception
     */
    private function set($name, Kernel\Bundle\Core $bundle = null)
    {
        // First get service if not an object:
        if (!is_object($bundle)) {
            $bundle = new $this->bundles[$name]();
        }

        // Set application and register it:
        $app = $this->getApp();
        $bundle->setApp($app);
        $bundle->initialize();
        $this->bundlesInstance[$name] = $bundle;
    }

    /**
     * @return void
     */
    public function initializeAll()
    {
        foreach (array_keys($this->bundles) as $name) {
            $this->get($name);
        }
    }

	/**
	 * @return Core[]
	 */
	public function getAll()
	{
        // Initialize all bundles
        $this->initializeAll();

        // Return all bundles
		return $this->bundlesInstance;
	}
}