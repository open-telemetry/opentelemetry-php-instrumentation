--TEST--
Check SpanAttribute name must be a non-empty string or null
--DESCRIPTION--
A null name is the constructor default, so it keys on the parameter name like a
bare #[SpanAttribute]. Any other non-string name is dropped, not replaced with
the parameter name: that would publish a plausible-looking wrong key. Coercing
scalars to strings is not an option either, because whether userland would
coerce depends on strict_types in the declaring file. An empty string is not a
valid attribute key.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/SpanAttribute.php';
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributes.php';
use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\SpanAttribute;

enum E: string
{
    case K = 'enum.key';
}

class Keys
{
    const STR = 'const.key';
    const INT = 7;
}

class Subject
{
    #[WithSpan]
    public function m(
        #[SpanAttribute(null)] string $null,
        #[SpanAttribute('')] string $empty,
        #[SpanAttribute(123)] string $int,
        #[SpanAttribute(1.5)] string $float,
        #[SpanAttribute(true)] string $bool,
        #[SpanAttribute(['x'])] string $array,
        #[SpanAttribute(E::K)] string $enum,
        #[SpanAttribute(Keys::INT)] string $int_const,
        #[SpanAttribute(Keys::STR)] string $str_const,
    ): void {
    }
}

(new Subject())->m('N', 'E', 'I', 'F', 'B', 'A', 'EN', 'IC', 'SC');
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["null"]=>
  string(1) "N"
  ["const.key"]=>
  string(2) "SC"
}
string(4) "post"
