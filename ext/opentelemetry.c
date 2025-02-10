
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "ext/standard/info.h"
#include "php_opentelemetry.h"
#include "opentelemetry_arginfo.h"
#include "otel_observer.h"
#include "stdlib.h"
#include "string.h"
#include "zend_attributes.h"
#include "zend_closures.h"

static int check_conflict(HashTable *registry, const char *extension_name) {
    if (!extension_name || !*extension_name) {
        return 0;
    }
    zend_module_entry *module_entry;
    ZEND_HASH_FOREACH_PTR(registry, module_entry) {
        if (strcmp(module_entry->name, extension_name) == 0) {
            php_error_docref(NULL, E_NOTICE,
                             "Conflicting extension found (%s), OpenTelemetry "
                             "extension will be disabled",
                             extension_name);
            return 1;
        }
    }
    ZEND_HASH_FOREACH_END();
    return 0;
}

static void check_conflicts() {
    int conflict_found = 0;
    char *input = OTEL_G(conflicts);

    if (!input || !*input) {
        return;
    }

    HashTable *registry = &module_registry;
    const char *s = NULL, *e = input;
    /** @see https://github.com/php/php-src/blob/php-8.2.9/Zend/zend_API.c#L3324
     */
    while (*e) {
        switch (*e) {
        case ' ':
        case ',':
            if (s) {
                size_t len = e - s;
                char *result = (char *)malloc((len + 1) * sizeof(char));
                strncpy(result, s, len);
                result[len] = '\0'; // null terminate
                if (check_conflict(registry, result)) {
                    conflict_found = 1;
                }
                s = NULL;
            }
            break;
        default:
            if (!s) {
                s = e;
            }
            break;
        }
        e++;
    }
    if (check_conflict(registry, s)) {
        conflict_found = 1;
    }

    OTEL_G(disabled) = conflict_found;
}

ZEND_DECLARE_MODULE_GLOBALS(opentelemetry)

PHP_INI_BEGIN()
// conflicting extensions. a comma-separated list, eg "ext1,ext2"
STD_PHP_INI_ENTRY("opentelemetry.conflicts", "", PHP_INI_ALL, OnUpdateString,
                  conflicts, zend_opentelemetry_globals, opentelemetry_globals)
STD_PHP_INI_ENTRY_EX("opentelemetry.validate_hook_functions", "On", PHP_INI_ALL,
                     OnUpdateBool, validate_hook_functions,
                     zend_opentelemetry_globals, opentelemetry_globals,
                     zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY_EX("opentelemetry.allow_stack_extension", "Off", PHP_INI_ALL,
                     OnUpdateBool, allow_stack_extension,
                     zend_opentelemetry_globals, opentelemetry_globals,
                     zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY_EX("opentelemetry.attr_hooks_enabled", "Off", PHP_INI_ALL,
                     OnUpdateBool, attr_hooks_enabled,
                     zend_opentelemetry_globals, opentelemetry_globals,
                     zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY_EX("opentelemetry.display_warnings", "Off", PHP_INI_ALL,
                     OnUpdateBool, display_warnings, zend_opentelemetry_globals,
                     opentelemetry_globals, zend_ini_boolean_displayer_cb)
STD_PHP_INI_ENTRY("opentelemetry.attr_pre_handler_function",
                  "OpenTelemetry\\API\\Instrumentation\\WithSpanHandler::pre",
                  PHP_INI_ALL, OnUpdateString, pre_handler_function_fqn,
                  zend_opentelemetry_globals, opentelemetry_globals)
STD_PHP_INI_ENTRY("opentelemetry.attr_post_handler_function",
                  "OpenTelemetry\\API\\Instrumentation\\WithSpanHandler::post",
                  PHP_INI_ALL, OnUpdateString, post_handler_function_fqn,
                  zend_opentelemetry_globals, opentelemetry_globals)
PHP_INI_END()

PHP_FUNCTION(OpenTelemetry_Instrumentation_hook) {
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
#if defined(ZTS) && defined(COMPILE_DL_OPENTELEMETRY)
    ZEND_TSRMLS_CACHE_UPDATE();
#endif

    REGISTER_INI_ENTRIES();

    check_conflicts();

    if (!OTEL_G(disabled)) {
        opentelemetry_observer_init(INIT_FUNC_ARGS_PASSTHRU);
    }

    return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(opentelemetry) {
    UNREGISTER_INI_ENTRIES();

    return SUCCESS;
}

PHP_MINFO_FUNCTION(opentelemetry) {
    php_info_print_table_start();
    php_info_print_table_row(2, "opentelemetry hooks",
                             OTEL_G(disabled) ? "disabled (conflict)"
                                              : "enabled");
    php_info_print_table_row(2, "extension version", PHP_OPENTELEMETRY_VERSION);
    php_info_print_table_end();
    DISPLAY_INI_ENTRIES();
}

PHP_GINIT_FUNCTION(opentelemetry) {
    ZEND_SECURE_ZERO(opentelemetry_globals, sizeof(*opentelemetry_globals));
}

zend_module_entry opentelemetry_module_entry = {
    STANDARD_MODULE_HEADER,
    "opentelemetry",
    ext_functions,
    PHP_MINIT(opentelemetry),
    PHP_MSHUTDOWN(opentelemetry),
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
