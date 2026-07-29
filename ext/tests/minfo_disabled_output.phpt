--TEST--
Check if phpinfo() shows disabled status when conflicting extension found
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=Core
--FILE--
<?php
ob_start();
phpinfo(INFO_MODULES);
$info = ob_get_clean();

preg_match('/opentelemetry hooks => (.+)/', $info, $matches);
var_dump(trim($matches[1]));
?>
--EXPECTF--
Notice: PHP Startup: Conflicting extension found (Core), OpenTelemetry extension will be disabled in %s
string(19) "disabled (conflict)"
