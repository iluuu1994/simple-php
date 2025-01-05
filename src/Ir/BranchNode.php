<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\ControlType;

/**
 * @property array{0: IfNode} $inputs
 */
class BranchNode extends ControlNode
{
    public function __construct(IfNode $ctrl, private bool $trueBranch)
    {
        parent::__construct([$ctrl]);
    }

    public function infer(): ControlType
    {
        $cond = $this->inputs[0]->inputs[1];
        $condType = $cond->infer();

        if ($condType instanceof ConstantType) {
            if (($this->trueBranch && $condType->value === 0)
             || (!$this->trueBranch && $condType->value !== 0)) {
                return ControlType::dead();
            }
        }

        return ControlType::alive();
    }

    public function idealize(): ?ControlNode
    {
        $if = $this->inputs[0];
        $cond = $if->inputs[1];
        $condType = $cond->infer();

        if (count($if->outputs) === 1) {
            return $if->inputs[0];
        } else if ($condType instanceof ConstantType) {
            if (($this->trueBranch && $condType->value !== 0)
             || (!$this->trueBranch && $condType->value === 0)) {
                return $if->inputs[0];
            } else {
                return new DeadNode();
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return $this->trueBranch ? 'True' : 'False';
    }
}
