<?php

namespace SimplePhp\Ir;

class ConstantNode extends DataNode
{
    public function __construct(
        public StartNode $start,
        public readonly int $value,
    ) {
        parent::__construct([$start]);
    }

    public function __toString(): string
    {
        return 'Constant ' . $this->value;
    }
}
