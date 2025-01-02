<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

/**
 * @property array{0: DataNode, 1: DataNode} $inputs
 */
class DivNode extends DataNode
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
            return new ConstantType($lhsType->value / $rhsType->value);
        }

        return new BotType();
    }

    public function __toString(): string
    {
        return 'Div';
    }

    public function print(): string
    {
        return '(' . $this->inputs[0]->print() . ' / ' . $this->inputs[1]->print() . ')';
    }
}
