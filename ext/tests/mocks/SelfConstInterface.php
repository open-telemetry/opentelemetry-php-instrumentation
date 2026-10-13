<?php

namespace OpenTelemetry\Test;

use OpenTelemetry\API\Instrumentation\SpanAttribute;
use OpenTelemetry\API\Instrumentation\WithSpan;

/**
 * Interfaces whose attributes use self:: to reference their own constants.
 *
 * The constants are declared AFTER the methods on purpose. PHP only const-folds
 * self::X if X is already in the active class's constants_table, so the usual
 * ordering folds the value against the interface and hides any scope bug. With
 * this ordering the arg survives as an IS_CONSTANT_AST and is evaluated at run
 * time, which is where the declaring scope has to be used.
 *
 * The implementing classes in SelfConstImpl.php redeclare the same constants
 * with different values, so resolving against the wrong scope is visible.
 */
interface SelfConstWithSpan
{
    #[WithSpan('iface-withspan', self::KIND)]
    public function go(): void;

    public const KIND = 1;
}

interface SelfConstSpanAttribute
{
    #[WithSpan]
    public function m(#[SpanAttribute(self::ATTR_NAME)] string $one): void;

    public const ATTR_NAME = 'iface.attr';
}
