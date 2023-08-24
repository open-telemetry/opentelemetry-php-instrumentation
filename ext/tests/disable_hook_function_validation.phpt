--TEST--
Disabling hook validation
--DESCRIPTION--
The post hook is invalid, because of the type-hint on $object. Runtime checking is disabled, so it is executed and
causes a fatal runtime error.
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.validate_hook_functions=Off
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello', post: fn(\Exception $object, array $params, string $return): string => 'replaced');

function hello(int $val) {
    return $val;
}

var_dump(hello(1));
?>
--EXPECTF--
%sArgument #1 ($object) must be of type Exception, null given%a