<?php
namespace Mu\Kernel;

use Mu\Kernel;

abstract class Application extends \Phalcon\Mvc\Application
{
    protected $boMultiLang = false;
    protected $controller;
    protected $route;
    protected $bundler;
    protected $statics = array();
    protected $updateFunctions = array();
    protected $installFunctions = array();
    protected $startMicrotime = 0;
    protected $defaultDbContext;
    protected $defaultDatabase;
    protected $siteUrl;
    protected $cookycryptKey = 'murloc1234567890';
    protected $extensions = array();
    protected $defaultServiceList = array(
        'log'           => '\Mu\Kernel\Log\Service',
        'trigger'       => '\Mu\Kernel\Trigger\Service',
        'toolbox'       => '\Mu\Kernel\Toolbox',
        'http'          => '\Mu\Kernel\Http\Service',
        'factory'       => '\Mu\Kernel\Factory',
        'route'         => '\Mu\Kernel\Route\Service',
        'config'        => '\Mu\Kernel\Config\Service',
        'error'         => '\Mu\Kernel\Error\Service',
        'cron'          => '\Mu\Kernel\Cron\Service',
        'localization'  => '\Mu\Kernel\Localization\Service',
        'site'          => '\Mu\Kernel\Site\Service',
        'view'          => '\Mu\Kernel\View\Service',
        'volt'          => '\Mu\Kernel\View\Volt\Service',
    );
    protected $serviceList = array();
    protected $projectList = array();

    private $environment = null;

    const ENVIRONMENT_LOCAL     = 'local';
    const ENVIRONMENT_PREPROD   = 'preprod';
    const ENVIRONMENT_PROD      = 'prod';

    /************************************************************************************
     **  INITIALISATION                                                                **
     ************************************************************************************/
    public function __construct($environment = null) {
        ob_start();
        $this->startMicrotime = microtime(true);
        if (isset($_GET['XHPROF'])) {
            \xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }

        // Force environment if needed:
        if (!empty($environment)) {
            $this->environment = $environment;
        }

        try {
            // Init:
            $this->initialize();

            $this->setExtensions(array('SPL', 'xhprof', 'redis', 'igbinary', 'Zend OPcache', 'phalcon'));

            #Register DI
            $this->registerServices();
            $this->loadConfiguration();
            $this->initConfiguration();
            #endregion

            #region Register Bundler
            $bundler = new Bundle\Bundler();
            $bundler->setApp($this);
            $this->setBundler($bundler);
            $this->registerBundles();
            #endregion
        } catch (Kernel\EndException $e) {
            // Normal exception (end of execution)
        }
    }

    public function start() {
        echo $this->handle()->getContent();
    }

    public function __destruct()
    {
        if (isset($_GET['XHPROF'])) {
            // stop profiler
            $xhprofData = \xhprof_disable();

            include_once VENDOR_PATH . "/facebook/xhprof/xhprof_lib/utils/xhprof_lib.php";
            include_once VENDOR_PATH . "/facebook/xhprof/xhprof_lib/utils/xhprof_runs.php";

            $xhprofPath = TEMP_PATH . '/xhprof/';
            if (!file_exists($xhprofPath)) {
                mkdir($xhprofPath, 0777, true);
            }
            $xhprofRuns = new \XHProfRuns_Default($xhprofPath);

            $id = uniqid();
            $xhprofRuns->save_run($xhprofData, strtolower(str_replace(' ', '_', $this->getName())), $id);
        }
    }

    /**
     * @throws Config\Exception
     */
    private function initConfiguration()
    {
        $config = $this->getConfigManager();
        $statics = $config->get('url.statics', array());

        foreach ($statics as $staticUrl) {
            $this->registerStatic($staticUrl);
        }
    }

    abstract public function getProjectName();

    abstract protected function initialize();

    abstract protected function loadConfiguration();

    abstract protected function registerBundles();

    abstract public function configureService($name, $service);

    protected function defineTriggers()
    {

    }

    protected function initializeUpdate()
    {
        // Initialize all bundles:
        $this->getBundler()->initializeAll();
        $dbS = $this->getDatabase();

        if ($dbS) {
            $this->registerUpdateFunction('db update', array($dbS, 'defaultUpdate'));
        }
    }

    protected function initializeInstall()
    {
        // Initialize all bundles:
        $this->getBundler()->initializeAll();
        $dbS = $this->getDatabase();

        if ($dbS) {
            $this->registerInstallFunction('reset', array($this, 'resetApp'));
            $this->registerInstallFunction('createStructure', array($this, 'createStructure'));
            $this->registerInstallFunction('createDefaultDataSet', array($this, 'createDefaultDataSet'));
        }
    }

    /**
     * @param $autoload
     */
    public function registerAutoload($autoload)
    {
        $this->getToolbox()->registerAutoload($autoload);
    }

    protected function registerServices()
    {
        // Create a DI
        $di = new \Phalcon\DI\FactoryDefault();

        foreach ($this->defaultServiceList as $name => $class) {
            $di[$name] = function() use ($name, $class) {
                $service = new $class();
                $service->setApp($this);
                $this->configureService($name, $service);

                return $service;
            };
        }

/*        // Set the database service
        $di['db'] = function() {
            return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                "host"     => "localhost",
                "username" => "root",
                "password" => "secret",
                "dbname"   => "tutorial"
            ));
        };*/

        $this->setDI($di);
    }

    /**
     * @return array
     */
    public function checkExtensions()
    {
        $extensions = $this->getExtensions();
        $extensionsLoaded = get_loaded_extensions();

        natcasesort($extensions);
        natcasesort($extensionsLoaded);
        $status = array();
        foreach($extensions as $extension) {
            $status[$extension] = (in_array($extension, $extensionsLoaded)) ? true : false;
        }

        return $status;
    }

    /************************************************************************************
     **  GETTERS                                                                       **
     ************************************************************************************/
    /**
     * @return bool
     */
    public function getEnvironment()
    {
        if ($this->environment!==null) {
            return $this->environment;
        }

        $environment = ini_get('docref_root');
        switch ($environment) {
            default:
                $this->environment = self::ENVIRONMENT_LOCAL;
                break;
            case self::ENVIRONMENT_LOCAL:
            case self::ENVIRONMENT_PREPROD:
            case self::ENVIRONMENT_PROD:
                $this->environment = $environment;
                break;
        }

        return $this->environment;
    }

    /**
     * @param $label
     * @return int
     */
    public function getProjectId($label)
    {
        if (!isset($this->projectIds)) {
            $this->projectIds = array_flip($this->projectList);
        }

        return ($this->projectIds[$label]) ? $this->projectIds[$label] : 0;
    }

    /**
     * @param $id
     * @return string
     */
    public function getProjectLabel($id)
    {
        return (isset($this->projectList[$id])) ? $this->projectList[$id] : 'default';
    }

    /**
     * @return bool
     */
    public function isEsiEnabled()
    {
        return $this->enableEsi;
    }

    public function isBoMultiLang() {
        return $this->boMultiLang;
    }

    /**
     * @return bool
     */
    public function isSsiEnabled()
    {
        return $this->enableSsi;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getSiteService()->getCurrentSiteUrl();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getProjectName();
    }

    /**
     * @return string
     */
    public function getDefaultDatabase()
    {
        return $this->defaultDatabase;
    }

    /**
     * @return string
     */
    public function getDefaultDbContext()
    {
        return $this->defaultDbContext;
    }

    /**
     * @return int
     */
    public function getStartTime()
    {
        return $this->startMicrotime;
    }

    /**
     * @return string
     */
    public function getExecTime()
    {
        return (microtime(true) - $this->startMicrotime) * 1000;
    }

    /**
     * @return Controller\Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return Route\Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return \Phalcon\DiInterface
     */
    public function getServicer()
    {
        return $this->getDI();
    }

    /**
     * @return Bundle\Bundler
     */
    public function getBundler()
    {
        return $this->bundler;
    }

    /**
     * @return Error\Service
     */
    public function getErrorService()
    {
        return $this->getServicer()->get('error');
    }

    /**
     * @return Cron\Service
     */
    public function getCronService()
    {
        return $this->getServicer()->get('cron');
    }

    /**
     * @return Log\Service
     */
    public function getLogger()
    {
        return $this->getServicer()->get('log');
    }

    /**
     * @return Db\Service
     */
    public function getDatabase()
    {
        return $this->getServicer()->get('database');
    }

    /**
     * @return Http\Service
     */
    public function getHttp()
    {
        return $this->getServicer()->get('http');
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->getServicer()->get('factory');
    }

    /**
     * @return Route\Service
     */
    public function getRouteManager()
    {
        return $this->getServicer()->get('route');
    }

    /**
     * @return Toolbox
     */
    public function getToolbox()
    {
        return $this->getServicer()->get('toolbox');
    }

    /**
     * @return View\Service
     */
    public function getViewManager()
    {
        return $this->getServicer()->get('view');
    }

    /**
     * @return Renderer\Service
     */
    public function getRendererManager()
    {
        return $this->getServicer()->get('renderer');
    }

    /**
     * @return Cache\Page\Service
     */
    public function getPageCache()
    {
        try {
            return $this->getServicer()->get('pageCache');
        } catch (Service\Exception $e) {
            return null;
        }
    }

    /**
     * @return Cache\Model\Service
     */
    public function getEntityCache()
    {
        try {
            return $this->getServicer()->get('entityCache');
        } catch (Service\Exception $e) {
            return null;
        }
    }

    /**
     * @return Kernel\Session\Service
     */
    public function getSession()
    {
        try {
            return $this->getServicer()->get('session');
        } catch (Service\Exception $e) {
            return null;
        }
    }

    /**
     * @return Kernel\Model\Service
     */
    public function getModelManager()
    {
        try {
            return $this->getServicer()->get('model');
        } catch (Service\Exception $e) {
            return null;
        }
    }

    /**
     * @return Kernel\Asset\Service
     */
    public function getAssetManager()
    {
        return $this->getServicer()->get('asset');
    }

    /**
     * @return Kernel\Config\Service
     */
    public function getConfigManager()
    {
        return $this->getServicer()->get('config');
    }

    /**
     * @return Kernel\Backoffice\Service
     */
    public function getBackofficeService()
    {
        try {
            return $this->getServicer()->get('backoffice');
        } catch (Service\Exception $e) {
            return null;
        }
    }

    /**
     * @return Kernel\Localization\Service
     */
    public function getLocalizationService()
    {
        try {
            return $this->getServicer()->get('localization');
        } catch (Service\Exception $e) {
            return null;
        }
    }

    /**
     * @return Kernel\Site\Service
     */
    public function getSiteService()
    {
        return $this->getServicer()->get('site');
    }

    /**
     * @return Kernel\Trigger\Service
     */
    public function getTriggerService()
    {
        return $this->getServicer()->get('trigger');
    }

    /**
     * @return string
     */
    private function getControllerClassname()
    {
        if ($this->getRoute()->getBundleName()) {
            return '\\Mu\\Bundle\\' . $this->getRoute()->getBundleName() . '\\Controller\\' . $this->getRoute(
            )->getControllerName();
        } else {
            return '\\Mu\\App\\Controller\\' . $this->getRoute()->getControllerName();
        }
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    /************************************************************************************
     **  SETTERS                                                                       **
     ************************************************************************************/

    /**
     * @param string $db
     */
    public function setDefaultDatabase($db)
    {
        $this->defaultDatabase = $db;
    }

    /**
     * @param string $context
     */
    public function setDefaultDbContext($context)
    {
        $this->defaultDbContext = $context;
    }

    /**
     * @param int $time
     */
    public function setStartTime($time)
    {
        $this->startMicrotime = (int)$time;
    }

    /**
     * @param Service\Servicer $sm
     */
    protected function setServicer(Service\Servicer $sm)
    {
        $this->servicer = $sm;
    }

    /**
     * @param Bundle\Bundler $bm
     */
    protected function setBundler(Bundle\Bundler $bm)
    {
        $this->bundler = $bm;
    }

    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /************************************************************************************
     **  DISPLAY                                                                       **
     ************************************************************************************/
    /**
     * @return Controller\Controller
     * @throws Controller\exception
     */
    private function generateControllerObject()
    {
        $classname = $this->getControllerClassname();
        if (!class_exists($classname)) {
            throw new Controller\Exception($classname, Controller\Exception::CLASS_NOT_FOUND);
        }

        return $this->getFactory()->getController($classname);
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @param bool $forceRedirection
     * @param bool $sendData
     * @return string
     */
    public function redirect($routeName, array $parameters = array(), $forceRedirection = false, $sendData = true)
    {
        if ($forceRedirection) {
            $url = $this->getRouteManager()->getUrl($routeName, $parameters);
            $response = $this->getHttp()->getResponse();
            $header   = $response->getHeader();
            $header->setLocation($url);
            if ($forceRedirection === 301) {
                $header->setCode(301);
            } else {
                $header->setCode(302);
            }

            // Send redirection:
            $response->send();
        }

        $request = $this->getHttp()->getRequest();
        foreach ($parameters as $key => $value) {
            $request->setParameter($key, Kernel\Http\Request::PARAM_TYPE_GET, $value);
        }
        $request->setParameter('rn', Kernel\Http\Request::PARAM_TYPE_GET, $routeName);
        $this->route = $this->getRouteManager()->selectRoute();

        return $this->dispatch($sendData);
    }

    /**
     * @param string $controllerName
     * @param string $fragmentName
     * @param array $parameters
     * @return string
     */
    public function fragmentRedirect($controllerName, $fragmentName, array $parameters = array())
    {
        $parameters[Kernel\Route\Service::FRAGMENT_PARAM] = $fragmentName;

        $content = $this->redirect($controllerName, $parameters, false, false);
        $this->getRouteManager()->reloadMainRoute();

        return $content;
    }

    /**
     * @param bool $sendData
     * @throws EndException
     * @return string
     */
    private function dispatch($sendData = true)
    {
        $this->controller = $this->generateControllerObject();

        // If the current route is an alias, set Response header code to 301:
        if ($this->getRoute()->isAlias()) {
            $this->getHttp()->getResponse()->setCode(301);
        }

        // Get HTTP objects:
        $http     = $this->getHttp();
        $response = $http->getResponse();

        // Fetch view content:
        $content  = $this->fetch();
        $content = $this->getController()->postRender($content);
        if (!$sendData) {
            return $content;
        }

        // Set response header and content:
        $renderer = $this->getRendererManager()->getHandler();
        $header   = $response->getHeader();
        $header->setContentType($renderer->getContentType());
        $response->setContent($content);
        $response->send();
        return true;
    }

    /**
     * @return string
     */
    private function fetch()
    {
        // If initialize return content, it's an error
        $controller = $this->getController();
        $error      = $controller->initialize();
        if ($error) {
            if ($error instanceof Kernel\View\View) {
                return $controller->getRenderer()->render($error);
            }
            return $error;
        }

        // Fetch fragment and skip controller if request is only for fragment
        $controller->preFetch();
        $fragmentView = $controller->fetchFragment();
        if ($fragmentView) {
            $renderer = $this->getRendererManager()->getHtmlHandler();  // Force HTML renderer for fragments
            $content  = $renderer->render($fragmentView);
            return $content;
        }

        $cacheManager = $this->getPageCache();
        if ($cacheManager && $cacheManager->isActive() && $controller->hasCache()) {
            $cacheKey = $controller->getCacheKey();

            try {
                return $cacheManager->get($cacheKey, $controller->getCacheTtl());
            } catch (Cache\Exception $e) {
                $content = $this->render($controller);
                $cacheManager->set($cacheKey, $content);
                return $content;
            }
        } else {
            return $this->render($controller);
        }
    }

    /**
     * Transform view content into correct rendering string
     * @param Kernel\Controller\Controller $controller
     * @return string
     */
    private function render(Kernel\Controller\Controller $controller)
    {
        $view = $controller->fetch();
        if (!$view) {
            return '';
        }

        return $controller->getRenderer()->render($view);
    }


    /************************************************************************************
     **  UPDATE                                                                        **
     ************************************************************************************/
    /**
     * @param array $functions
     */
    public function setUpdateFunctions(array $functions)
    {
        $this->updateFunctions = array();
        foreach ($functions as $function) {
            if (is_callable($function)) {
                $this->updateFunctions[] = $function;
            }
        }
    }

    /**
     * @param string $name
     * @param $function
     */
    public function registerUpdateFunction($name, $function)
    {
        if (is_callable($function)) {
            $this->updateFunctions[$name] = $function;
        }
    }

    /**
     * @return array
     */
    private function getUpdateFunctions()
    {
        return $this->updateFunctions;
    }

    /**
     * @param string $name
     * @param $function
     */
    public function registerInstallFunction($name, $function)
    {
        if (is_callable($function)) {
            $this->installFunctions[$name] = $function;
        }
    }

    /**
     * @return array
     */
    private function getInstallFunctions()
    {
        return $this->installFunctions;
    }

    /**
     * @param string $stdOut
     * @throws Exception
     */
    public function update($stdOut = '\print')
    {
        if (!defined('MU_CONSOLE')) {
            throw new Exception('', Exception::CONSOLE_EXPECTED);
        }
        $this->initializeUpdate();

        foreach ($this->getUpdateFunctions() as $name => $function) {
            call_user_func($stdOut, 'Executing ' . $name . '...');
            call_user_func($function, $stdOut);
            call_user_func($stdOut, 'Done');
        }
    }

    /**
     * @param string $stdOut
     * @throws Exception
     */
    public function install($stdOut = '\print')
    {
        define('INSTALLING', true);
        if (!defined('MU_CONSOLE')) {
            throw new Exception('', Exception::CONSOLE_EXPECTED);
        }
        $this->initializeInstall();

        foreach ($this->getInstallFunctions() as $name => $function) {
            call_user_func($stdOut, 'Executing ' . $name . '...');
            call_user_func($function, $stdOut);
            call_user_func($stdOut, 'Done');
        }
    }

    /**
     * @param string $stdOut
     * @return bool
     */
    protected function resetApp($stdOut = '\print')
    {
        $dbManager = $this->getDatabase();
        if (!$dbManager) {
            call_user_func($stdOut, 'No database found');
            return true;
        }

        call_user_func($stdOut, 'Removing database...');
        $handler = $dbManager->getHandler($this->getDefaultDbContext());
        $handler->query('CREATE DATABASE IF NOT EXISTS `sys_empty`');
        $handler->query('USE `sys_empty`');
        $handler->query('DROP DATABASE IF EXISTS `' . $this->getDefaultDatabase() . '`');
        $handler->query('CREATE DATABASE `' . $this->getDefaultDatabase() . '`');
        $handler->query('USE `' . $this->getDefaultDatabase() . '`');
        $handler->query('DROP DATABASE `sys_empty`');

        return true;
    }

    /**
     * @param string $stdOut
     * @return bool
     */
    protected function createStructure($stdOut = '\print')
    {
        $return = $this->getServicer()->createStructure($stdOut);

        return $return;
    }

    protected function createDefaultDataSet($stdOut = '\print')
    {
        $this->getDatabase()->defaultUpdate($stdOut, false);
        $return = $this->getServicer()->createDefaultDataSet($stdOut);

        return $return;
    }

    /**
     * @param string $file
     * @return string
     * @throws Exception
     */
    public function getUrlStatic($file)
    {
        $statics = $this->getStaticList();
        if (!is_array($statics)) {
            throw new Exception('', Exception::NO_STATIC_REGISTRED);
        }

        $nbStatic = count($statics);
        if (!$nbStatic) {
            throw new Exception('', Exception::NO_STATIC_REGISTRED);
        }

        if ($nbStatic > 1) {
            $pos = md5($file);
            $pos = ord($pos{8}) % $nbStatic;

            $baseUrl = $statics[$pos];
        } else {
            $baseUrl = reset($statics);
        }

        return $baseUrl . '/' . $file;
    }

    /**
     * Get a site url by site name
     * @param $siteName
     * @return string
     */
    public function getUrlSite($siteName)
    {
        return $this->getSiteService()->getSiteUrl($this->getSiteService()->getSiteId($siteName));
    }

    /**
     * @param string $url
     */
    public function registerStatic($url)
    {
        $this->statics[] = $url;
    }

    /**
     * @return array
     */
    public function getStaticList() {
        return $this->statics;
    }

    /**
     * @return string
     */
    public function getCookycryptKey()
    {
        return $this->cookycryptKey;
    }
}
