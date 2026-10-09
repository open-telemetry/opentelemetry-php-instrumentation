<?php

namespace OpenTelemetry\Test;

use OpenTelemetry\API\Instrumentation\SpanAttribute;
use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Trace\SpanKind;

/**
 * Attributes whose args reference class constants.
 *
 * Must be autoloaded, so that SpanKind is NOT in the class table when this
 * file is compiled. PHP then stores the args as unevaluated IS_CONSTANT_AST
 * zvals, which is the case being tested.
 */
class ConstArgs
{
    #[WithSpan('positional', SpanKind::KIND_PRODUCER)]
    public function positional(): void
    {
    }

    #[WithSpan('named', span_kind: SpanKind::KIND_PRODUCER)]
    public function named(): void
    {
    }

    #[WithSpan('in-attributes', SpanKind::KIND_INTERNAL, ['key' => SpanKind::KIND_PRODUCER])]
    public function inAttributes(): void
    {
    }

    #[WithSpan('undefined-const', SpanKind::DOES_NOT_EXIST)]
    public function undefinedConst(): void
    {
    }

    #[WithSpan]
    public function spanAttributeName(#[SpanAttribute(SpanKind::ATTR_NAME)] string $one): void
    {
    }
}
