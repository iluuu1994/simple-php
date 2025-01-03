<?php

namespace SimplePhp\Ir;

class BranchNode extends ControlNode
{
    public function __construct(ControlNode $ctrl, private string $name)
    {
        parent::__construct([$ctrl]);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
