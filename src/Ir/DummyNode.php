<?php

namespace SimplePhp\Ir;

use SimplePhp\UnexpectedError;

final class DummyNode extends Node
{
    private static ?self $instance = null;

    private function __construct()
    {
        parent::__construct([]);
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public function __toString(): string
    {
        throw new UnexpectedError();
    }
}
