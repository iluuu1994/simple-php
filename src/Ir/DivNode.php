<?php

namespace SimplePhp\Ir;

class DivNode extends DataNode
{
    public function __construct(DataNode $lhs, DataNode $rhs)
    {
        parent::__construct([$lhs, $rhs]);
    }

    public function __toString(): string
    {
        return 'Div';
    }
}
