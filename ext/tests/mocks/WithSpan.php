<?php

namespace OpenTelemetry\API\Instrumentation;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION|Attribute::TARGET_METHOD)]
final class WithSpan
{
    public function __construct(
        public readonly ?string $span_name = null,
        public readonly ?int $span_kind = null,
        public readonly array $attributes = [],
    ) {
    }
}