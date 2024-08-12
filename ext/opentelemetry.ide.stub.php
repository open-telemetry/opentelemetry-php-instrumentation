<?php

namespace OpenTelemetry\Instrumentation;

/**
 * @param string|null $class The (optional) hooked function's class. Null for a global/built-in function.
 * @param string $function The hooked function's name.
 * @param \Closure|null $pre function($class, array $params, string $class, string $function, ?string $filename, ?int $lineno, ?array $span_args, ?array $span_attributes): $params
 *        You may optionally return modified parameters.
 * @param \Closure|null $post function($class, array $params, $returnValue, ?Throwable $exception): $returnValue
 *        You may optionally return modified return value.
 * @return bool Whether the observer was successfully added
 *
 * @see https://github.com/open-telemetry/opentelemetry-php-instrumentation
 */
function hook(
    string|null $class,
    string $function,
    ?\Closure $pre = null,
    ?\Closure $post = null,
): bool {}

#[\Attribute(\Attribute::TARGET_FUNCTION|\Attribute::TARGET_METHOD)]
final class WithSpan
{
    public function __construct(
        ?string $span_name = null,
        ?int $span_type = null,
        array $attributes = [],
    ){}
}

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class SpanAttribute
{
    public function __construct(
        public ?string $name = null,
    ){}
}