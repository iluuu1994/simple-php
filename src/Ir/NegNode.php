<?php

namespace SimplePhp\Ir;

class NegNode extends DataNode
{
    public function __construct(DataNode $value)
    {
        parent::__construct([$value]);
    }

    public function __toString(): string
    {
        return 'Neg';
    }
}
