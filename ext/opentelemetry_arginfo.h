/* This is a generated file, edit the .stub.php file instead.
 * Stub hash: f4707b118ba43575214908e608c9c465bdc1edc2 */

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(
    arginfo_OpenTelemetry_Instrumentation_hook, 0, 2, _IS_BOOL, 0)
    ZEND_ARG_TYPE_INFO(0, class, IS_STRING, 1)
    ZEND_ARG_TYPE_INFO(0, function, IS_STRING, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, pre, Closure, 1, "null")
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, post, Closure, 1, "null")
ZEND_END_ARG_INFO()

ZEND_FUNCTION(hook);

static const zend_function_entry ext_functions[] = {
    ZEND_NS_FE("OpenTelemetry\\Instrumentation", hook,
               arginfo_OpenTelemetry_Instrumentation_hook) ZEND_FE_END};

static const zend_function_entry
    class_OpenTelemetry_Instrumentation_WithSpan_methods[] = {ZEND_FE_END};

static const zend_function_entry
    class_OpenTelemetry_Instrumentation_SpanAttribute_methods[] = {ZEND_FE_END};

static zend_class_entry *
register_class_OpenTelemetry_Instrumentation_WithSpan(void) {
    zend_class_entry ce, *class_entry;

    INIT_NS_CLASS_ENTRY(ce, "OpenTelemetry\\Instrumentation", "WithSpan",
                        class_OpenTelemetry_Instrumentation_WithSpan_methods);
    class_entry = zend_register_internal_class_ex(&ce, NULL);
    class_entry->ce_flags |= ZEND_ACC_FINAL;

    return class_entry;
}

static zend_class_entry *
register_class_OpenTelemetry_Instrumentation_SpanAttribute(void) {
    zend_class_entry ce, *class_entry;

    INIT_NS_CLASS_ENTRY(
        ce, "OpenTelemetry\\Instrumentation", "SpanAttribute",
        class_OpenTelemetry_Instrumentation_SpanAttribute_methods);
    class_entry = zend_register_internal_class_ex(&ce, NULL);
    class_entry->ce_flags |= ZEND_ACC_FINAL;

    return class_entry;
}
