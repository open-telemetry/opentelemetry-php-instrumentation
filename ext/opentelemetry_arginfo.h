/* This is a generated file, edit the .stub.php file instead.
 * Stub hash: a909b08f77c774d518ce9fbf5975f4f65a79235c */

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_OpenTelemetry_Instrumentation_hook, 0, 2, _IS_BOOL, 0)
	ZEND_ARG_TYPE_INFO(0, class, IS_STRING, 1)
	ZEND_ARG_TYPE_INFO(0, function, IS_STRING, 0)
	ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, pre, Closure, 1, "null")
	ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, post, Closure, 1, "null")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_OpenTelemetry_Instrumentation_observeAll, 0, 0, _IS_BOOL, 0)
	ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, pre, Closure, 1, "null")
	ZEND_ARG_OBJ_INFO_WITH_DEFAULT_VALUE(0, post, Closure, 1, "null")
ZEND_END_ARG_INFO()


ZEND_FUNCTION(OpenTelemetry_Instrumentation_hook);
ZEND_FUNCTION(OpenTelemetry_Instrumentation_observeAll);


static const zend_function_entry ext_functions[] = {
	ZEND_NS_FALIAS("OpenTelemetry\\Instrumentation", hook, OpenTelemetry_Instrumentation_hook, arginfo_OpenTelemetry_Instrumentation_hook)
	ZEND_NS_FALIAS("OpenTelemetry\\Instrumentation", observeAll, OpenTelemetry_Instrumentation_observeAll, arginfo_OpenTelemetry_Instrumentation_observeAll)
	ZEND_FE_END
};
