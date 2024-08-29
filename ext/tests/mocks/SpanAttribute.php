<?php

namespace OpenTelemetry\API\Instrumentation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SpanAttribute
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}