
#ifdef HAVE_CONFIG_H
#include "../ext_config.h"
#endif

#include <php.h>
#include "../php_ext.h"
#include "../ext.h"

#include <Zend/zend_operators.h>
#include <Zend/zend_exceptions.h>
#include <Zend/zend_interfaces.h>

#include "kernel/main.h"
#include "kernel/fcall.h"
#include "kernel/memory.h"
#include "kernel/time.h"
#include "kernel/object.h"
#include "kernel/array.h"
#include "kernel/operators.h"


ZEPHIR_INIT_CLASS(Silence_Application) {

	ZEPHIR_REGISTER_CLASS(Silence, Application, silence, application, silence_application_method_entry, ZEND_ACC_EXPLICIT_ABSTRACT_CLASS);

	zend_declare_property_bool(silence_application_ce, SL("boMultiLang"), 0, ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("controller"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("route"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("servicer"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("bundler"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("statics"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("updateFunctions"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("installFunctions"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_long(silence_application_ce, SL("startMicrotime"), 0, ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("defaultDbContext"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_bool(silence_application_ce, SL("production"), 1, ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_bool(silence_application_ce, SL("enableEsi"), 0, ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_bool(silence_application_ce, SL("enableSsi"), 0, ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("defaultDatabase"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("siteUrl"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_string(silence_application_ce, SL("cookycryptKey"), "murloc1234567890", ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("extensions"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("defaultServiceList"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("serviceList"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("projectList"), ZEND_ACC_PROTECTED TSRMLS_CC);

	zend_declare_property_null(silence_application_ce, SL("environment"), ZEND_ACC_PRIVATE TSRMLS_CC);

	silence_application_ce->create_object = zephir_init_properties_Silence_Application;
	zend_declare_class_constant_string(silence_application_ce, SL("ENVIRONMENT_LOCAL"), "local" TSRMLS_CC);

	zend_declare_class_constant_string(silence_application_ce, SL("ENVIRONMENT_PREPROD"), "preprod" TSRMLS_CC);

	zend_declare_class_constant_string(silence_application_ce, SL("ENVIRONMENT_PROD"), "prod" TSRMLS_CC);

	return SUCCESS;

}

/************************************************************************************
 **  INITIALISATION                                                                **
 ************************************************************************************/
PHP_METHOD(Silence_Application, __construct) {

	zephir_nts_static zend_class_entry *_5 = NULL, *_6 = NULL;
	zval *_4;
	int ZEPHIR_LAST_CALL_STATUS;
	zval *environment, *_0 = NULL, *_GET, *_1 = NULL, *_2, *_3, *servicer, *bundler;

	ZEPHIR_MM_GROW();
	zephir_get_global(&_GET, SS("_GET") TSRMLS_CC);
	zephir_fetch_params(1, 1, 0, &environment);




	/* try_start_1: */

		ZEPHIR_CALL_FUNCTION(NULL, "ob_start", NULL, 1);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_INIT_VAR(_0);
		zephir_microtime(_0, ZEPHIR_GLOBAL(global_true) TSRMLS_CC);
		zephir_update_property_this(this_ptr, SL("startMicrotime"), _0 TSRMLS_CC);
		if (zephir_array_isset_string(_GET, SS("XHPROF"))) {
			ZEPHIR_INIT_VAR(_1);
			ZEPHIR_GET_CONSTANT(_1, "XHPROF_FLAGS_CPU");
			ZEPHIR_INIT_VAR(_2);
			ZEPHIR_GET_CONSTANT(_2, "XHPROF_FLAGS_MEMORY");
			ZEPHIR_INIT_VAR(_3);
			zephir_add_function_ex(_3, _1, _2 TSRMLS_CC);
			ZEPHIR_CALL_FUNCTION(NULL, "\xhprof_enable", NULL, 0, _3);
			zephir_check_call_status_or_jump(try_end_1);
		}
		if (!(ZEPHIR_IS_EMPTY(environment))) {
			zephir_update_property_this(this_ptr, SL("environment"), environment TSRMLS_CC);
		}
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "initialize", NULL, 0);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_INIT_VAR(_4);
		zephir_create_array(_4, 5, 0 TSRMLS_CC);
		ZEPHIR_INIT_NVAR(_1);
		ZVAL_STRING(_1, "SPL", 1);
		zephir_array_fast_append(_4, _1);
		ZEPHIR_INIT_NVAR(_1);
		ZVAL_STRING(_1, "xhprof", 1);
		zephir_array_fast_append(_4, _1);
		ZEPHIR_INIT_NVAR(_1);
		ZVAL_STRING(_1, "redis", 1);
		zephir_array_fast_append(_4, _1);
		ZEPHIR_INIT_NVAR(_1);
		ZVAL_STRING(_1, "igbinary", 1);
		zephir_array_fast_append(_4, _1);
		ZEPHIR_INIT_NVAR(_1);
		ZVAL_STRING(_1, "Zend OPcache", 1);
		zephir_array_fast_append(_4, _1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "setextensions", NULL, 0, _4);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_INIT_VAR(servicer);
		if (!_5) {
			_5 = zend_fetch_class(SL("Silence\\Service\\Servicer"), ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
		}
		object_init_ex(servicer, _5);
		if (zephir_has_constructor(servicer TSRMLS_CC)) {
			ZEPHIR_CALL_METHOD(NULL, servicer, "__construct", NULL, 0);
			zephir_check_call_status_or_jump(try_end_1);
		}
		ZEPHIR_CALL_METHOD(NULL, servicer, "setapp", NULL, 0, this_ptr);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "setservicer", NULL, 0, servicer);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "registerservices", NULL, 0);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "loadconfiguration", NULL, 0);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "initconfiguration", NULL, 0);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_INIT_VAR(bundler);
		if (!_6) {
			_6 = zend_fetch_class(SL("Silence\\Bundle\\Bundler"), ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
		}
		object_init_ex(bundler, _6);
		if (zephir_has_constructor(bundler TSRMLS_CC)) {
			ZEPHIR_CALL_METHOD(NULL, bundler, "__construct", NULL, 0);
			zephir_check_call_status_or_jump(try_end_1);
		}
		ZEPHIR_CALL_METHOD(NULL, bundler, "setapp", NULL, 0, this_ptr);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "setbundler", NULL, 0, bundler);
		zephir_check_call_status_or_jump(try_end_1);
		ZEPHIR_CALL_METHOD(NULL, this_ptr, "registerbundles", NULL, 0);
		zephir_check_call_status_or_jump(try_end_1);

	try_end_1:

	if (EG(exception)) {
		ZEPHIR_INIT_NVAR(_0);
		ZEPHIR_CPY_WRT(_0, EG(exception));
		if (zephir_instance_of_ev(_0, silence_endexception_ce TSRMLS_CC)) {
			zend_clear_exception(TSRMLS_C);
		}
	}
	ZEPHIR_MM_RESTORE();

}

PHP_METHOD(Silence_Application, initialize) {

}

PHP_METHOD(Silence_Application, setExtensions) {

}

PHP_METHOD(Silence_Application, registerServices) {

}

PHP_METHOD(Silence_Application, loadConfiguration) {

}

PHP_METHOD(Silence_Application, initConfiguration) {

}

PHP_METHOD(Silence_Application, setBundler) {

}

PHP_METHOD(Silence_Application, registerBundles) {

}

PHP_METHOD(Silence_Application, setServicer) {

}

static zend_object_value zephir_init_properties_Silence_Application(zend_class_entry *class_type TSRMLS_DC) {

		zval *_4;
		zval *_0, *_1 = NULL, *_2, *_3, *_5, *_6, *_7, *_8;

		ZEPHIR_MM_GROW();
	
	{
		zval *this_ptr = NULL;
		ZEPHIR_CREATE_OBJECT(this_ptr, class_type);
		_0 = zephir_fetch_nproperty_this(this_ptr, SL("projectList"), PH_NOISY_CC);
		if (Z_TYPE_P(_0) == IS_NULL) {
			ZEPHIR_INIT_VAR(_1);
			array_init(_1);
			zephir_update_property_this(this_ptr, SL("projectList"), _1 TSRMLS_CC);
		}
		_2 = zephir_fetch_nproperty_this(this_ptr, SL("serviceList"), PH_NOISY_CC);
		if (Z_TYPE_P(_2) == IS_NULL) {
			ZEPHIR_INIT_NVAR(_1);
			array_init(_1);
			zephir_update_property_this(this_ptr, SL("serviceList"), _1 TSRMLS_CC);
		}
		_3 = zephir_fetch_nproperty_this(this_ptr, SL("defaultServiceList"), PH_NOISY_CC);
		if (Z_TYPE_P(_3) == IS_NULL) {
			ZEPHIR_INIT_VAR(_4);
			zephir_create_array(_4, 12, 0 TSRMLS_CC);
			add_assoc_stringl_ex(_4, SS("log"), SL("\Mu\Kernel\Log\Service"), 1);
			add_assoc_stringl_ex(_4, SS("trigger"), SL("\Mu\Kernel\Trigger\Service"), 1);
			add_assoc_stringl_ex(_4, SS("toolbox"), SL("\Mu\Kernel\Toolbox"), 1);
			add_assoc_stringl_ex(_4, SS("http"), SL("\Mu\Kernel\Http\Service"), 1);
			add_assoc_stringl_ex(_4, SS("factory"), SL("\Mu\Kernel\Factory"), 1);
			add_assoc_stringl_ex(_4, SS("route"), SL("\Mu\Kernel\Route\Service"), 1);
			add_assoc_stringl_ex(_4, SS("config"), SL("\Mu\Kernel\Config\Service"), 1);
			add_assoc_stringl_ex(_4, SS("error"), SL("\Mu\Kernel\Error\Service"), 1);
			add_assoc_stringl_ex(_4, SS("cron"), SL("\Mu\Kernel\Cron\Service"), 1);
			add_assoc_stringl_ex(_4, SS("localization"), SL("\Mu\Kernel\Localization\Service"), 1);
			add_assoc_stringl_ex(_4, SS("site"), SL("\Mu\Kernel\Site\Service"), 1);
			add_assoc_stringl_ex(_4, SS("renderer"), SL("\Mu\Kernel\Renderer\Service"), 1);
			zephir_update_property_this(this_ptr, SL("defaultServiceList"), _4 TSRMLS_CC);
		}
		_5 = zephir_fetch_nproperty_this(this_ptr, SL("extensions"), PH_NOISY_CC);
		if (Z_TYPE_P(_5) == IS_NULL) {
			ZEPHIR_INIT_NVAR(_1);
			array_init(_1);
			zephir_update_property_this(this_ptr, SL("extensions"), _1 TSRMLS_CC);
		}
		_6 = zephir_fetch_nproperty_this(this_ptr, SL("installFunctions"), PH_NOISY_CC);
		if (Z_TYPE_P(_6) == IS_NULL) {
			ZEPHIR_INIT_NVAR(_1);
			array_init(_1);
			zephir_update_property_this(this_ptr, SL("installFunctions"), _1 TSRMLS_CC);
		}
		_7 = zephir_fetch_nproperty_this(this_ptr, SL("updateFunctions"), PH_NOISY_CC);
		if (Z_TYPE_P(_7) == IS_NULL) {
			ZEPHIR_INIT_NVAR(_1);
			array_init(_1);
			zephir_update_property_this(this_ptr, SL("updateFunctions"), _1 TSRMLS_CC);
		}
		_8 = zephir_fetch_nproperty_this(this_ptr, SL("statics"), PH_NOISY_CC);
		if (Z_TYPE_P(_8) == IS_NULL) {
			ZEPHIR_INIT_NVAR(_1);
			array_init(_1);
			zephir_update_property_this(this_ptr, SL("statics"), _1 TSRMLS_CC);
		}
		ZEPHIR_MM_RESTORE();
		return Z_OBJVAL_P(this_ptr);
	}

}

