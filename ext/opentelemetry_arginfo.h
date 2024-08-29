/* This is a generated file, edit the .stub.php file instead.
 * Stub hash: aa29142596154400c530f1194a7f29fbb9036929 */

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(
    arginfo_OpenTelemetry_Instrumentation_hook, 0, 2, _IS_BOOL, 0)
    ZEND_ARG_TYPE_INFO(0, class, IS_STRING, 1)
    ZEND_ARG_TYPE_INFO(0, function, IS_STRING, 0)
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, pre, Closure, 1, "null")
    ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, post, Closure, 1, "null")
ZEND_END_ARG_INFO()

ZEND_FUNCTION(OpenTelemetry_Instrumentation_hook);

static const zend_function_entry ext_functions[] = {ZEND_NS_FALIAS(
    "OpenTelemetry\\Instrumentation", hook, OpenTelemetry_Instrumentation_hook,
    arginfo_OpenTelemetry_Instrumentation_hook) ZEND_FE_END};
