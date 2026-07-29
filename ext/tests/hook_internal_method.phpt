--TEST--
Check hooks work on internal class methods
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    'DateTime',
    'format',
    fn() => var_dump('PRE'),
    fn() => var_dump('POST')
);

$dt = new DateTime('2023-01-01');
$result = $dt->format('Y');
var_dump($result);
?>
--EXPECT--
string(3) "PRE"
string(4) "POST"
string(4) "2023"
