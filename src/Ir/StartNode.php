<?php

namespace SimplePhp\Ir;

class StartNode extends ControlNode
{
    public function __construct()
    {
        parent::__construct([]);
    }

    public function __toString(): string
    {
        return 'Start';
    }
}
