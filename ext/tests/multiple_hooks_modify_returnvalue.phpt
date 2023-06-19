--TEST--
Check if hooks receive modified returnvalue
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return): string => ++$return);
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return): string => ++$return);
//\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', null, fn(): string => 'b');

function helloWorld() {
    // below instruction (or any equivalent) is needed
    // to prevent optimizer to optimize out
    // helloWorld call and generate DO_UCALL
    // otherwise hooks will not be invoked
    // as there will be no function
    echo ' ';
    return 'a';
}

var_dump(helloWorld());
?>
--EXPECT--
string(1) "c"
