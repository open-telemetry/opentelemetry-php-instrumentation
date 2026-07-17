--TEST--
Check filename and lineno are null for internal functions
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'array_map',
    function($obj, array $params, ?string $class, string $function, ?string $filename, ?int $lineno) {
        var_dump($filename);
        var_dump($lineno);
    }
);

array_map('strtoupper', ['hello']);
?>
--EXPECT--
NULL
NULL
