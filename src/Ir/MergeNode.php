<?php

namespace SimplePhp\Ir;

class MergeNode extends ControlNode
{
    public function __toString(): string
    {
        return 'Merge';
    }
}
