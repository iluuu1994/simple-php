<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;
use SimplePhp\Syntax\Parser;

class ConstantNode extends DataNode
{
    public static ?StartNode $startNode = null;

    public function __construct(
        public readonly int $value,
    ) {
        $start = Parser::getStart();
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

    public function print(): string
    {
        return (string) $this->value;
    }
}
