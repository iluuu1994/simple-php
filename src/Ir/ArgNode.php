<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\Type;

class ArgNode extends DataNode
{
    public function __construct(ControlNode $ctrl, private ?Type $type)
    {
        parent::__construct([$ctrl]);
    }

    public function infer(): Type
    {
        if ($this->type === null) {
            $this->type = new BotType();
        }
        return $this->type;
    }

    public function __toString(): string
    {
        return 'Arg';
    }

    public function print(): string
    {
        return 'arg';
    }
}
