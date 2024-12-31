<?php

namespace SimplePhp\Ir;

class MulNode extends DataNode
{
    public function __construct(DataNode $lhs, DataNode $rhs)
    {
        parent::__construct([$lhs, $rhs]);
    }

    public function __toString(): string
    {
        return 'Mul';
    }
}
