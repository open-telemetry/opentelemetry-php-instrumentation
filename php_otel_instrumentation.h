
#ifndef PHP_OTEL_INSTRUMENTATION_H
# define PHP_OTEL_INSTRUMENTATION_H

extern zend_module_entry otel_instrumentation_module_entry;
# define phpext_otel_instrumentation_ptr &otel_instrumentation_module_entry

ZEND_BEGIN_MODULE_GLOBALS(otel_instrumentation)
    HashTable *observer_class_lookup;
    HashTable *observer_function_lookup;
    HashTable *observer_aggregates;
ZEND_END_MODULE_GLOBALS(otel_instrumentation)

ZEND_EXTERN_MODULE_GLOBALS(otel_instrumentation)

# define OTEL_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(otel_instrumentation, v)

# define PHP_OPENTELEMETRY_VERSION "1.0.0beta2"
# define PHP_OTEL_INSTRUMENTATION_VERSION PHP_OPENTELEMETRY_VERSION

# if defined(ZTS) && defined(COMPILE_DL_OTEL_INSTRUMENTATION)
ZEND_TSRMLS_CACHE_EXTERN()
# endif

#endif	/* PHP_OTEL_INSTRUMENTATION_H */
