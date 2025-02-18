<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ControlType;

class StartNode extends ControlNode
{
    public function __construct()
    {
        parent::__construct([]);
    }

    public function infer(): ControlType
    {
        return ControlType::alive();
    }

    public function __toString(): string
    {
        return 'Start';
    }
}
