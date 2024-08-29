<?php

namespace OpenTelemetry\API\Instrumentation;

class WithSpanHandler
{
    public static function pre(): void
    {
        var_dump('pre');
        var_dump(func_get_args()[7]);
    }
    public static function post(): void
    {
        var_dump('post');
    }
}