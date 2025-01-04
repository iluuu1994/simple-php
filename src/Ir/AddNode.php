<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;
use SimplePhp\Syntax\Parser;

/**
 * @property array{0: DataNode, 1: DataNode} $inputs
 */
class AddNode extends DataNode
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
            return new ConstantType($lhsType->value + $rhsType->value);
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

        /* a + 0 is a no-op. */
        if ($rhsType instanceof ConstantType && $rhsType->value === 0) {
            return $lhs;
        }

        /* node + node becomes node * 2. */
        if ($lhs === $rhs) {
            return new MulNode($lhs, (new ConstantNode(2))->peephole());
        }

        /* Pull adds to the left. */
        if (!$lhs instanceof AddNode && $rhs instanceof AddNode) {
            return new AddNode($rhs, $lhs);
        }

        /* Transform (a + b) + (c + d) into (((a + b) + c) + d) */
        if ($rhs instanceof AddNode) {
            assert($lhs instanceof AddNode);
            return new AddNode((new AddNode($lhs, $rhs->inputs[0]))->peephole(), $rhs->inputs[1]);
        }

        if (!$lhs instanceof AddNode) {
            if ($this->shouldSwapOperands($lhs, $rhs)) {
                return new AddNode($rhs, $lhs);
            }
            return null;
        }

        /* We're only left with (a + b) + c, . */

        /* Replace (a + const) + const with a + (const + const), which will fold. */
        if ($lhs->inputs[1] instanceof ConstantNode && $rhs instanceof ConstantNode) {
            return new AddNode($lhs->inputs[0], (new AddNode($lhs->inputs[1], $rhs))->peephole());
        }

        /* Check if we should swap b and c. */
        if ($this->shouldSwapOperands($lhs->inputs[1], $rhs)) {
            return new AddNode((new AddNode($lhs->inputs[0], $rhs))->peephole(), $lhs->inputs[1]);
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
        return 'Add';
    }

    public function print(): string
    {
        return '(' . $this->inputs[0]->print() . ' + ' . $this->inputs[1]->print() . ')';
    }
}
