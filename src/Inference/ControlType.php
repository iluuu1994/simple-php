<?php

namespace SimplePhp\Inference;

final class ControlType extends Type
{
    private static ?self $aliveInstance = null;
    private static ?self $deadInstance = null;

    private function __construct() {}

    public static function alive(): self
    {
        return self::$aliveInstance ??= new self();
    }

    public static function dead(): self
    {
        return self::$deadInstance ??= new self();
    }
}
