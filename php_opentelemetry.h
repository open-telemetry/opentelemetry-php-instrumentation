
#ifndef PHP_OPENTELEMETRY_H
#define PHP_OPENTELEMETRY_H

extern zend_module_entry opentelemetry_module_entry;
#define phpext_opentelemetry_ptr &opentelemetry_module_entry

ZEND_BEGIN_MODULE_GLOBALS(opentelemetry)
    HashTable *observer_class_lookup;
    HashTable *observer_function_lookup;
    HashTable *observer_aggregates;
ZEND_END_MODULE_GLOBALS(opentelemetry)

ZEND_EXTERN_MODULE_GLOBALS(opentelemetry)

#define OTEL_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(opentelemetry, v)

#define PHP_OPENTELEMETRY_VERSION "1.0.0beta5"

#if defined(ZTS) && defined(COMPILE_DL_OPENTELEMETRY)
ZEND_TSRMLS_CACHE_EXTERN()
#endif

#endif /* PHP_OPENTELEMETRY_H */
