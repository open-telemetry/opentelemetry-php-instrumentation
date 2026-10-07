--TEST--
Static method class name is not over-freed across repeated observed calls
--DESCRIPTION--
Regression test for https://github.com/open-telemetry/opentelemetry-php/issues/2032
func_get_this_or_called_scope() must take a reference on the called scope's name
before passing it to the hooks as $target, because observer_begin() and
observer_end() release every param afterwards.

An interned name absorbs the underflow and hides the defect, so the name must
stay refcounted: hence eval() (opcache does not persist eval'd code), opcache
enabled (otherwise the name is interned request-locally), and a name built at
run time rather than a literal (a literal would be interned with this file, and
eval() would reuse it).
--EXTENSIONS--
opentelemetry
opcache
--INI--
opcache.enable_cli=1
--FILE--
<?php
// Without opcache this test passes even against the defect, so fail loudly
// rather than silently testing nothing.
if (!ini_get('opcache.enable_cli')) {
    echo "FAIL: opcache must be enabled for this test to be meaningful\n";
}

// strrev() is opaque to the compiler, so this is not folded to an interned literal.
$class = 'Demo' . strrev('321');

eval("class {$class} { public static function hello(): int { return 1; } }");

\OpenTelemetry\Instrumentation\hook(
    $class,
    'hello',
    static function (mixed $target): void {},
    static function (mixed $target): void {},
);

// Each call drops a reference it never took, freeing the name partway through.
for ($i = 0; $i < 100; $i++) {
    $class::hello();
}

// Reclaim the memory; if the name was freed, these overwrite it.
$filler = [];
for ($i = 0; $i < 5000; $i++) {
    $filler[] = str_repeat('X', 24);
}

// Compared against $class, never a literal: a literal would be interned and
// eval() would reuse it, passing despite the defect.
var_dump((new ReflectionClass($class))->getName() === $class);
?>
--EXPECT--
bool(true)
