--TEST--
Check hooks on multiple methods of the same class
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook('Demo', 'methodA', fn() => var_dump('pre-A'));
\OpenTelemetry\Instrumentation\hook('Demo', 'methodB', fn() => var_dump('pre-B'));

class Demo {
    public function methodA(): void { var_dump('A'); }
    public function methodB(): void { var_dump('B'); }
}

$d = new Demo();
$d->methodA();
$d->methodB();
?>
--EXPECT--
string(5) "pre-A"
string(1) "A"
string(5) "pre-B"
string(1) "B"
