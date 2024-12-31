<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

class NegNode extends DataNode
{
    public function __construct(DataNode $value)
    {
        parent::__construct([$value]);
    }

    public function infer(): Type
    {
        $value = $this->inputs[0];
        assert($value instanceof DataNode);
        $valueType = $value->infer();

        if ($valueType instanceof ConstantType) {
            return new ConstantType(-$valueType->value);
        }

        return new BotType();
    }

    public function __toString(): string
    {
        return 'Neg';
    }
}
