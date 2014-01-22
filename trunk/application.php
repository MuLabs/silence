<?php
namespace Mu\Kernel;

use Mu\Kernel;

abstract class Application
{
	private $controller;
	private $route;
	private $servicer;
	private $bundler;
	private $statics = array();
	private $updateFunctions = array();
	private $installFunctions = array();
	private $startMicrotime = 0;
	private $defaultDbContext;
	protected $production = true;
	protected $enableEsi = true;
	protected $defaultDatabase;
	protected $siteUrl;
	protected $projectName;

	const VIEW_JSON = 'json';


	/************************************************************************************
	 **  INITIALISATION                                                                **
	 ************************************************************************************/
	public function __construct()
	{
		try {
			ob_start();
			$this->startMicrotime = microtime(true);
			if (isset($_GET['XHPROF'])) {
				\xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
			}
			$this->initialize();

			#region Register Servicer
			$servicer = new Service\Servicer();
			$servicer->setApp($this);
			$this->setServicer($servicer);
			$this->registerDefaultServices();
			$this->loadConfiguration();
			$this->registerCustomServices();
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

	private function registerDefaultServices()
	{
		$servicer = $this->getServicer();
		$servicer->register('log', '\Mu\Kernel\Log\Service');
		$servicer->register('toolbox', '\Mu\Kernel\Toolbox');
		$servicer->register('http', '\Mu\Kernel\Http\Service');
		$servicer->register('factory', '\Mu\Kernel\Factory');
		$servicer->register('route', '\Mu\Kernel\Route\Service');
		$servicer->register('config', '\Mu\Kernel\Config\Service');
		$servicer->register('error', '\Mu\Kernel\Error\Service', array('type' => '\Mu\Kernel\Error\Service'));
	}

	abstract protected function initialize();

	abstract protected function loadConfiguration();

	abstract protected function registerCustomServices();

	abstract protected function registerBundles();

	protected function initializeUpdate()
	{
		$this->registerUpdateFunction('file update', array($this, 'defaultUpdate'));
	}

	protected function initializeInstall()
	{
		$this->registerInstallFunction('reset', array($this, 'resetApp'));
		$this->registerInstallFunction('createStructure', array($this, 'createStructure'));
		$this->registerInstallFunction('createDefaultDataSet', array($this, 'createDefaultDataSet'));
	}

	/**
	 * @param $autoload
	 */
	protected function registerAutoload($autoload)
	{
		$this->getToolbox()->registerAutoload($autoload);
	}


	/************************************************************************************
	 **  GETTERS                                                                       **
	 ************************************************************************************/
	/**
	 * @return bool
	 */
	public function isProduction()
	{
		return $this->production;
	}

	/**
	 * @return bool
	 */
	public function isEsiEnabled()
	{
		return $this->enableEsi;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->siteUrl;
	}

	/**
	 * @param string $siteUrl
	 */
	public function setSiteUrl($siteUrl)
	{
		$this->siteUrl = $siteUrl;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->projectName;
	}

	/**
	 * @param string $projectName
	 */
	public function setProjectName($projectName)
	{
		$this->projectName = $projectName;
	}

	/**
	 * @return string
	 */
	public function getDefaultDatabase()
	{
		return $this->defaultDatabase;
	}

	/**
	 * @param string $db
	 */
	public function setDefaultDatabase($db)
	{
		$this->defaultDatabase = $db;
	}

	/**
	 * @return string
	 */
	public function getDefaultDbContext()
	{
		return $this->defaultDbContext;
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
	 * @return Service\Servicer
	 */
	public function getServicer()
	{
		return $this->servicer;
	}

	/**
	 * @param Service\Servicer $sm
	 */
	protected function setServicer(Service\Servicer $sm)
	{
		$this->servicer = $sm;
	}

	/**
	 * @return Bundle\Bundler
	 */
	public function getBundler()
	{
		return $this->bundler;
	}

	/**
	 * @param Bundle\Bundler $bm
	 */
	protected function setBundler(Bundle\Bundler $bm)
	{
		$this->bundler = $bm;
	}

	/**
	 * @return Error\Service
	 */
	public function getErrorService()
	{
		return $this->getServicer()->get('error');
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
	 * @return Cache\Page\Service
	 */
	public function getPageCache()
	{
		try {
			return $this->getServicer()->get('page_cache');
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
			return $this->getServicer()->get('entity_cache');
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

	/**
	 * @return string
	 */
	private function getControllerFilename()
	{
		if ($this->getRoute()->getBundleName()) {
			return BUNDLE_PATH . '/' . strtolower($this->getRoute()->getBundleName()) . '/controller/' . strtolower(
				$this->getRoute()->getControllerName()
			) . '.php';
		} else {
			return APP_CONTROLLER_PATH . '/' . strtolower($this->getRoute()->getControllerName()) . '.php';
		}
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

	public function start()
	{
		try {
			$this->route = $this->getRouteManager()->selectRoute();
			$this->dispatch();
		} catch (Kernel\EndException $e) {
			// Normal exception (end of execution)
		}
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
			$this->getHttp()->getResponse()->getHeader()->setLocation($url);
			$this->getHttp()->getResponse()->send();
		}
		foreach ($parameters as $key => $value) {
			$this->getHttp()->getRequest()->setParameter($key, Kernel\Http\Request::PARAM_TYPE_POST, $value);
			$this->getHttp()->getRequest()->setParameter($key, Kernel\Http\Request::PARAM_TYPE_GET, $value);
		}
		$this->getHttp()->getRequest()->setParameter('rn', Kernel\Http\Request::PARAM_TYPE_GET, $routeName);
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

		return $this->redirect($controllerName, $parameters, false, false);
	}

	/**
	 * @param bool $sendData
	 * @throws EndException
	 * @return string
	 */
	private function dispatch($sendData = true)
	{
		$this->includeControllerFile();
		$this->controller = $this->generateControllerObject();

		// If the current route is an alias, set Response header code to 301:
		if ($this->getRoute()->isAlias()) {
			$this->getHttp()->getResponse()->setCode(301);
		}

		$response = $this->getHttp()->getResponse();
		$content = $this->fetch();

		if (!$sendData) {
			return $content;
		}

		$response->setContent($content);
		$response->send();
	}

	/**
	 * @throws Controller\Exception
	 */
	private function includeControllerFile()
	{
		$filename = $this->getControllerFilename();

		if (!file_exists($filename)) {
			throw new Controller\Exception($filename, Controller\Exception::FILE_NOT_FOUND);
		}

		require_once($filename);
	}

	/**
	 * @return string
	 */
	private function fetch()
	{
		$this->getController()->initialize();
		// Fetch fragment and skip controller if request is only for fragment
		$fragmentContent = $this->getController()->fetchFragment();
		if (is_string($fragmentContent)) {
			return $fragmentContent;
		}

		$cacheManager = $this->getPageCache();
		$cacheKey = get_class($this->getController()) . '|' . $this->getController()->getCacheKey();

		if ($cacheManager
			&& $this->getController()->hasCache()
		) {
			try {
				return $cacheManager->get($cacheKey, $this->getController()->getCacheTtl());
			} catch (Cache\Exception $e) {
				$content = $this->getController()->fetch();
				$cacheManager->set($cacheKey, $content);
				return $content;
			}
		} else {
			return $this->getController()->fetch();
		}
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
	 */
	protected function defaultUpdate($stdOut)
	{
		$this->getToolbox()->removeLimits();

		if (!file_exists(APP_UPDATE_DONE_PATH)) {
			mkdir(APP_UPDATE_DONE_PATH, 0777, true);
		}

		$updateDone = array();
		$dirh = opendir(APP_UPDATE_DONE_PATH);
		while ($filename = readdir($dirh)) {
			if (is_file(APP_UPDATE_DONE_PATH . '/' . $filename)) {
				$updateDone[$filename] = 1;
			}
		}

		$updateTodo = array();
		$dirh = opendir(APP_UPDATE_PATH);
		while ($filename = readdir($dirh)) {
			if (is_file(APP_UPDATE_PATH . '/' . $filename) && empty($updateDone[$filename])) {
				$updateTodo[] = $filename;
			}
		}
		sort($updateTodo);
		$count = count($updateTodo);
		call_user_func($stdOut, $count . ' updates to do...');

		$i = 1;
		// Used into update files
		$handler = $this->getDatabase()->getHandler($this->getDefaultDbContext());
		$handler->disableLogs();
		foreach ($updateTodo as $filename) {
			call_user_func($stdOut, 'Start update ' . $i . '/' . $count . ' : ' . $filename);
			require_once(APP_UPDATE_PATH . '/' . $filename);
			touch(APP_UPDATE_DONE_PATH . '/' . $filename);
			call_user_func($stdOut, 'End update ' . $i++ . '/' . $count);
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
		return $this->getModelManager()->createStructure($stdOut);
	}

	protected function createDefaultDataSet($stdOut = '\print')
	{
		return $this->getModelManager()->createDefaultDataSet($stdOut);
	}

	/**
	 * @param string $file
	 * @return string
	 * @throws Exception
	 */
	public function getUrlStatic($file)
	{
		if (!is_array($this->statics)) {
			throw new Exception('', Exception::NO_STATIC_REGISTRED);
		}

		$nbStatic = count($this->statics);
		if (!$nbStatic) {
			throw new Exception('', Exception::NO_STATIC_REGISTRED);
		}

		if ($nbStatic > 1) {
			$pos = md5($file);
			$pos = ord($pos{8}) % $nbStatic;

			$baseUrl = $this->statics[$pos];
		} else {
			$baseUrl = reset($this->statics);
		}

		return $baseUrl . '/' . $file;
	}

	/**
	 * @param string $url
	 */
	public function registerStatic($url)
	{
		$this->statics[] = $url;
	}

	/**
	 * Automatically load a JSON View
	 * @return Kernel\View\Json\View
	 */
	public function getJsonView()
	{
		// Try to return json view or initialize it:
		try {
			$service = $this->getServicer()->get(self::VIEW_JSON);
		} catch (Kernel\Service\Exception $e) {
			$service = new Kernel\View\Json\Service();
			$this->getServicer()->register(self::VIEW_JSON, $service);
		}

		// Return view object:
		return $service->getView();
	}
}
