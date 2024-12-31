<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

class DivNode extends DataNode
{
    public function __construct(DataNode $lhs, DataNode $rhs)
    {
        parent::__construct([$lhs, $rhs]);
    }

    public function infer(): Type
    {
        $lhs = $this->inputs[0];
        $rhs = $this->inputs[1];
        assert($lhs instanceof DataNode);
        assert($rhs instanceof DataNode);
        $lhsType = $lhs->infer();
        $rhsType = $rhs->infer();

        if ($lhsType instanceof ConstantType && $rhsType instanceof ConstantType) {
            return new ConstantType($lhsType->value / $rhsType->value);
        }

        return new BotType();
    }

    public function __toString(): string
    {
        return 'Div';
    }
}
