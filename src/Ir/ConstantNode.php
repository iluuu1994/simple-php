<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

class ConstantNode extends DataNode
{
    public static ?StartNode $startNode = null;

    public function __construct(
        public StartNode $start,
        public readonly int $value,
    ) {
        parent::__construct([$start]);
    }

    public function infer(): Type
    {
        return new ConstantType($this->value);
    }

    public function __toString(): string
    {
        return 'Constant ' . $this->value;
    }
}
