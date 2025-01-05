<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ControlType;

class DeadNode extends ControlNode
{
    public function __construct()
    {
        parent::__construct([]);
    }

    public function infer(): ControlType
    {
        return ControlType::dead();
    }

    public function __toString(): string
    {
        return 'Dead';
    }
}
