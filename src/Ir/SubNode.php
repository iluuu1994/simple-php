<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

/**
 * @property array{0: DataNode, 1: DataNode} $inputs
 */
class SubNode extends DataNode
{
    public function __construct(DataNode $lhs, DataNode $rhs)
    {
        parent::__construct([$lhs, $rhs]);
    }

    public function infer(): Type
    {
        $lhsType = $this->inputs[0]->infer();
        $rhsType = $this->inputs[1]->infer();

        if ($lhsType instanceof ConstantType && $rhsType instanceof ConstantType) {
            return new ConstantType($lhsType->value - $rhsType->value);
        }

        return new BotType();
    }

    public function idealize(): ?DataNode
    {
        $lhsType = $this->inputs[0]->infer();
        $rhsType = $this->inputs[1]->infer();

        /* Turn a - 1 to a + (-1), which will encourage folding. */
        if ($lhsType instanceof ConstantType) {
            return new AddNode(new ConstantNode(-$lhsType->value), $this->inputs[1]);
        } else if ($rhsType instanceof ConstantType) {
            return new AddNode($this->inputs[0], new ConstantNode(-$rhsType->value));
        } else {
            return null;
        }
    }

    public function __toString(): string
    {
        return 'Sub';
    }

    public function print(): string
    {
        return '(' . $this->inputs[0]->print() . ' - ' . $this->inputs[1]->print() . ')';
    }
}
