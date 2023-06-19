--TEST--
Check if calling die or exit will finish gracefully
--EXTENSIONS--
opentelemetry
--FILE--

<?php

use function OpenTelemetry\Instrumentation\hook;

class TestClass {
    public static function countFunction(): void
    {
       for ($i = 1; $i <= 300; $i++) {
            if ($i === 200) {
                die('exit!');
            }
       }
    }
}

hook(
    'TestClass',
    'countFunction',
    null,
    post: static function ($object, array $params, mixed $ret, ?\Throwable $exception ) {}
);

try{
TestClass::countFunction();
}
catch(Exception) {}
// Comment out line below and revert fix in order to trigger segfault
// reproduction frequency depends on platform
catch(TypeError) {}
?>

--EXPECT--
exit!
