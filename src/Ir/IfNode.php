<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ControlType;

/**
 * @property array{0: ControlNode, 1: DataNode} $inputs
 */
class IfNode extends ControlNode
{
    public function __construct(ControlNode $ctrl, DataNode $condition)
    {
        parent::__construct([$ctrl, $condition]);
    }

    public function infer(): ControlType
    {
        return $this->inputs[0]->infer();
    }

    public function __toString(): string
    {
        return 'If';
    }
}
