<?php

namespace SimplePhp\Inference;

class ConstantType extends Type
{
    public function __construct(
        public readonly int $value,
    ) {}
}
