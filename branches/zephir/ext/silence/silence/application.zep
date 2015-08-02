namespace Silence;

use Silence;

abstract class Application
{
    protected boMultiLang = false;
    protected controller;
    protected route;
    protected servicer;
    protected bundler;
    protected statics = [];
    protected updateFunctions = [];
    protected installFunctions = [];
    protected startMicrotime = 0;
    protected defaultDbContext;
    protected production = true;
    protected enableEsi = false;
    protected enableSsi = false;
    protected defaultDatabase;
    protected siteUrl;
    protected cookycryptKey = "murloc1234567890";
    protected extensions = [];
    protected defaultServiceList = [
        "log": "\Mu\Kernel\Log\Service",
        "trigger": "\Mu\Kernel\Trigger\Service",
        "toolbox": "\Mu\Kernel\Toolbox",
        "http": "\Mu\Kernel\Http\Service",
        "factory": "\Mu\Kernel\Factory",
        "route": "\Mu\Kernel\Route\Service",
        "config": "\Mu\Kernel\Config\Service",
        "error": "\Mu\Kernel\Error\Service",
        "cron": "\Mu\Kernel\Cron\Service",
        "localization": "\Mu\Kernel\Localization\Service",
        "site": "\Mu\Kernel\Site\Service",
        "renderer": "\Mu\Kernel\Renderer\Service"
    ];
    
    protected serviceList = [];
    protected projectList = [];

    private environment;

    const ENVIRONMENT_LOCAL     = "local";
    const ENVIRONMENT_PREPROD   = "preprod";
    const ENVIRONMENT_PROD      = "prod";

    /************************************************************************************
     **  INITIALISATION                                                                **
     ************************************************************************************/
    public function __construct(environment) -> void
    {
        try {
            ob_start();
            let this->startMicrotime = microtime(true);
            if (isset(_GET["XHPROF"])) {
                \xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
            }

            // Force environment if needed:
            if (!empty(environment)) {
                let this->environment = environment;
            }

            // Init:
            this->initialize();

            this->setExtensions(["SPL", "xhprof", "redis", "igbinary", "Zend OPcache"]);

            var servicer;
            let servicer = new Service\Servicer();
            servicer->setApp(this);
            this->setServicer(servicer);

            this->registerServices();
            this->loadConfiguration();
            this->initConfiguration();

            var bundler;
            let bundler = new Bundle\Bundler();
            bundler->setApp(this);
            this->setBundler(bundler);
            this->registerBundles();
        } catch Silence\EndException {
            // Normal exception (end of execution)
        }
    }

    abstract function initialize();
    abstract function setExtensions(array extensions);
    abstract function registerServices();
    abstract function loadConfiguration();
    abstract function initConfiguration();
    abstract function setBundler(bundler);
    abstract function registerBundles();
    abstract function setServicer(servicer);
}