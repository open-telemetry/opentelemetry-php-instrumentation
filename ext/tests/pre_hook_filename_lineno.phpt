--TEST--
Check pre hook receives correct filename and line number
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    function($obj, array $params, ?string $class, string $function, ?string $filename, ?int $lineno) {
        var_dump($function);
        var_dump(str_contains($filename, 'pre_hook_filename_lineno.php'));
        var_dump($lineno);
    }
);

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECT--
string(10) "helloWorld"
bool(true)
int(12)
string(4) "CALL"
