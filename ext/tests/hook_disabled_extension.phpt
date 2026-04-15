--TEST--
Check hooks do not fire when extension is disabled by conflict
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=Core
--FILE--
<?php
var_dump(\OpenTelemetry\Instrumentation\hook(null, 'hello', fn() => var_dump('PRE')));

function hello() {
    var_dump('CALL');
}

hello();
?>
--EXPECTF--
Notice: PHP Startup: Conflicting extension found (Core), OpenTelemetry extension will be disabled in %s
bool(false)
string(4) "CALL"
