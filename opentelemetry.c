
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_opentelemetry.h"
#include "opentelemetry_arginfo.h"
#include "otel_observer.h"
#include "zend_closures.h"

ZEND_DECLARE_MODULE_GLOBALS(opentelemetry)

PHP_FUNCTION(hook) {
    zend_string *class_name;
    zend_string *function_name;
    zval *pre = NULL;
    zval *post = NULL;

    ZEND_PARSE_PARAMETERS_START(2, 4)
        Z_PARAM_STR_OR_NULL(class_name)
        Z_PARAM_STR(function_name)
        Z_PARAM_OPTIONAL
        Z_PARAM_OBJECT_OF_CLASS_OR_NULL(pre, zend_ce_closure)
        Z_PARAM_OBJECT_OF_CLASS_OR_NULL(post, zend_ce_closure)
    ZEND_PARSE_PARAMETERS_END();

    RETURN_BOOL(add_observer(class_name, function_name, pre, post));
}

PHP_RINIT_FUNCTION(opentelemetry) {
#if defined(ZTS) && defined(COMPILE_DL_OPENTELEMETRY)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif

    observer_globals_init();

    return SUCCESS;
}

PHP_RSHUTDOWN_FUNCTION(opentelemetry) {
    observer_globals_cleanup();

    return SUCCESS;
}

PHP_MINIT_FUNCTION(opentelemetry) {
    opentelemetry_observer_init(INIT_FUNC_ARGS_PASSTHRU);

    return SUCCESS;
}

PHP_MINFO_FUNCTION(opentelemetry) {
    php_info_print_table_start();
    php_info_print_table_header(2, "opentelemetry support", "enabled");
    php_info_print_table_row(2, "extension version", PHP_OPENTELEMETRY_VERSION);
    php_info_print_table_end();
}

PHP_GINIT_FUNCTION(opentelemetry) {
    ZEND_SECURE_ZERO(opentelemetry_globals, sizeof(*opentelemetry_globals));
}

zend_module_entry opentelemetry_module_entry = {
    STANDARD_MODULE_HEADER,
    "opentelemetry",
    ext_functions,
    PHP_MINIT(opentelemetry),
    NULL,
    PHP_RINIT(opentelemetry),
    PHP_RSHUTDOWN(opentelemetry),
    PHP_MINFO(opentelemetry),
    PHP_OPENTELEMETRY_VERSION,
    PHP_MODULE_GLOBALS(opentelemetry),
    PHP_GINIT(opentelemetry),
    NULL,
    NULL,
    STANDARD_MODULE_PROPERTIES_EX,
};

#ifdef COMPILE_DL_OPENTELEMETRY
#ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
#endif
ZEND_GET_MODULE(opentelemetry)
#endif
