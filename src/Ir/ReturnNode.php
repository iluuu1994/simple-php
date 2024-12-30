<?php

namespace SimplePhp\Ir;

class ReturnNode extends ControlNode
{
    public function __construct(Node $ctrl, DataNode $value)
    {
        parent::__construct([$ctrl, $value]);
    }

    public function __toString(): string
    {
        return 'Return';
    }
}
