--TEST--
Check validate_hook_functions=On rejects invalid pre hook signature
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.validate_hook_functions=On
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'hello',
    static function (array $params) {
        // first param should be object/null, not array
        var_dump('pre should not run');
    },
    null
);

function hello() {
    var_dump('CALL');
}

hello();
?>
--EXPECTF--
Warning: hello(): OpenTelemetry: pre hook invalid signature, class=null function=hello in %s on line %d
string(4) "CALL"
