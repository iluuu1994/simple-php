<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ControlType;

/**
 * @property array{0: ControlNode, 1: DataNode} $inputs
 */
class ReturnNode extends ControlNode
{
    public function __construct(ControlNode $ctrl, DataNode $value)
    {
        parent::__construct([$ctrl, $value]);
    }

    public function infer(): ControlType
    {
        return $this->inputs[0]->infer();
    }

    public function idealize(): ?ControlNode
    {
        $type = $this->infer();
        if ($type === ControlType::dead()) {
            return $this->inputs[0];
        }
        return null;
    }

    public function __toString(): string
    {
        return 'Return';
    }
}
