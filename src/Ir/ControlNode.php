<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ControlType;

abstract class ControlNode extends Node
{
    abstract public function infer(): ControlType;

    public function peephole(): self
    {
        $idealized = $this->idealize();
        if ($idealized !== null) {
            $idealized = $idealized->peephole();
            $this->dce($idealized);
            return $idealized;
        }

        return $this;
    }

    public function idealize(): ?self
    {
        return null;
    }
}
