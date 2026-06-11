--TEST--
Check if phpinfo() displays opentelemetry module info correctly
--EXTENSIONS--
opentelemetry
--FILE--
<?php
ob_start();
phpinfo(INFO_MODULES);
$info = ob_get_clean();

// Check for enabled status
preg_match('/opentelemetry hooks => (\w+)/', $info, $matches);
var_dump($matches[1]);

// Check for extension version
preg_match('/extension version => ([\d.]+)/', $info, $matches);
var_dump(!empty($matches[1]));

// Check INI settings are displayed
var_dump(strpos($info, 'opentelemetry.conflicts') !== false);
var_dump(strpos($info, 'opentelemetry.validate_hook_functions') !== false);
var_dump(strpos($info, 'opentelemetry.allow_stack_extension') !== false);
var_dump(strpos($info, 'opentelemetry.attr_hooks_enabled') !== false);
?>
--EXPECT--
string(7) "enabled"
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
