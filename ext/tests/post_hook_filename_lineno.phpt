--TEST--
Check post hook receives correct filename and line number
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    null,
    function($obj, array $params, mixed $ret, ?Throwable $e, ?string $class, string $function, ?string $filename, ?int $lineno) {
        var_dump($function);
        var_dump(str_contains($filename, 'post_hook_filename_lineno.php'));
        var_dump($lineno);
    }
);

function helloWorld() {
    return 'hello';
}

helloWorld();
?>
--EXPECT--
string(10) "helloWorld"
bool(true)
int(13)
