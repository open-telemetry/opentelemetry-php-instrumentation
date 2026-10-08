<?php

namespace OpenTelemetry\Test;

/**
 * Implementations that redeclare the interface constants with different values
 * and carry no attributes of their own.
 *
 * The attribute is found on the interface method, so it must be evaluated
 * against the interface's scope. If the implementation's scope is used instead,
 * these values (99 / impl.attr) surface rather than the interface's.
 */
class SelfConstWithSpanImpl implements SelfConstWithSpan
{
    public const KIND = 99;

    public function go(): void
    {
    }
}

class SelfConstSpanAttributeImpl implements SelfConstSpanAttribute
{
    public const ATTR_NAME = 'impl.attr';

    public function m(string $one): void
    {
    }
}
