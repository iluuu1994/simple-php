<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

/**
 * @property array{0: DataNode, 1: DataNode} $inputs
 */
class MulNode extends DataNode
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
            return new ConstantType($lhsType->value * $rhsType->value);
        }

        return new BotType();
    }

    public function idealize(): ?DataNode
    {
        $lhs = $this->inputs[0];
        $rhs = $this->inputs[1];
        $lhsType = $lhs->infer();
        $rhsType = $rhs->infer();
        /* Handled by peephole. */
        assert(!$lhsType instanceof ConstantType || !$rhsType instanceof ConstantType);

        /* a * 1 is a no-op. */
        if ($rhsType instanceof ConstantType && $rhsType->value === 1) {
            return $lhs;
        }

        if ($this->shouldSwapOperands($lhs, $rhs)) {
            return new MulNode($rhs, $lhs);
        }

        return null;
    }

    private function shouldSwapOperands(Node $lhs, Node $rhs): bool
    {
        if ($rhs instanceof ConstantNode) {
            return false;
        }
        if ($lhs instanceof ConstantNode) {
            return true;
        }
        return $lhs->id > $rhs->id;
    }

    public function __toString(): string
    {
        return 'Mul';
    }

    public function print(): string
    {
        return '(' . $this->inputs[0]->print() . ' * ' . $this->inputs[1]->print() . ')';
    }
}
