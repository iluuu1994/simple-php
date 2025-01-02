<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

class CompNode extends DataNode
{
    public function __construct(public readonly CompKind $kind, DataNode $lhs, DataNode $rhs)
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
            switch ($this->kind) {
                case CompKind::Equal:
                    $result = $lhsType->value === $rhsType->value;
                    break;
                case CompKind::Lower:
                    $result = $lhsType->value < $rhsType->value;
                    break;
                case CompKind::LowerEqual:
                    $result = $lhsType->value <= $rhsType->value;
                    break;
            }
            return new ConstantType($result ? 1 : 0);
        }

        return new BotType();
    }

    public function __toString(): string
    {
        return $this->kind->name;
    }

    public function print(): string
    {
        $lhs = $this->inputs[0];
        $rhs = $this->inputs[1];
        assert($lhs instanceof DataNode);
        assert($rhs instanceof DataNode);
        switch ($this->kind) {
            case CompKind::Equal:
                $compSymbol = '==';
                break;
            case CompKind::Lower:
                $compSymbol = '<';
                break;
            case CompKind::LowerEqual:
                $compSymbol = '<=';
                break;
        }
        return $lhs->print() . ' ' . $compSymbol . ' ' . $rhs->print();
    }
}
