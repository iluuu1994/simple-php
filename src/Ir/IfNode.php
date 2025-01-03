<?php

namespace SimplePhp\Ir;

class IfNode extends ControlNode
{
    public function __construct(ControlNode $ctrl, DataNode $condition)
    {
        parent::__construct([$ctrl, $condition]);
    }

    public function __toString(): string
    {
        return 'If';
    }
}
