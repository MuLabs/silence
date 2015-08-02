
extern zend_class_entry *silence_application_ce;

ZEPHIR_INIT_CLASS(Silence_Application);

PHP_METHOD(Silence_Application, __construct);
PHP_METHOD(Silence_Application, initialize);
PHP_METHOD(Silence_Application, setExtensions);
PHP_METHOD(Silence_Application, registerServices);
PHP_METHOD(Silence_Application, loadConfiguration);
PHP_METHOD(Silence_Application, initConfiguration);
PHP_METHOD(Silence_Application, setBundler);
PHP_METHOD(Silence_Application, registerBundles);
PHP_METHOD(Silence_Application, setServicer);
static zend_object_value zephir_init_properties_Silence_Application(zend_class_entry *class_type TSRMLS_DC);

ZEND_BEGIN_ARG_INFO_EX(arginfo_silence_application___construct, 0, 0, 1)
	ZEND_ARG_INFO(0, environment)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_silence_application_setextensions, 0, 0, 1)
	ZEND_ARG_ARRAY_INFO(0, extensions, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_silence_application_setbundler, 0, 0, 1)
	ZEND_ARG_INFO(0, bundler)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_silence_application_setservicer, 0, 0, 1)
	ZEND_ARG_INFO(0, servicer)
ZEND_END_ARG_INFO()

ZEPHIR_INIT_FUNCS(silence_application_method_entry) {
	PHP_ME(Silence_Application, __construct, arginfo_silence_application___construct, ZEND_ACC_PUBLIC|ZEND_ACC_CTOR)
	PHP_ME(Silence_Application, initialize, NULL, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, setExtensions, arginfo_silence_application_setextensions, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, registerServices, NULL, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, loadConfiguration, NULL, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, initConfiguration, NULL, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, setBundler, arginfo_silence_application_setbundler, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, registerBundles, NULL, ZEND_ACC_ABSTRACT)
	PHP_ME(Silence_Application, setServicer, arginfo_silence_application_setservicer, ZEND_ACC_ABSTRACT)
	PHP_FE_END
};
