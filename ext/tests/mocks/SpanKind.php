<?php

namespace OpenTelemetry\API\Trace;

/**
 * Stand-in for the real SpanKind + attribute name constants.
 *
 * This lives in its own file, and tests must load it lazily (via an
 * autoloader, or an include that runs after the attributed function is
 * compiled). If it is already in the class table when the attribute is
 * compiled, PHP const-folds the value and the test no longer covers the
 * IS_CONSTANT_AST path.
 */
final class SpanKind
{
    public const KIND_INTERNAL = 1;
    public const KIND_PRODUCER = 3;

    public const ATTR_NAME = 'custom.attribute.name';
}
