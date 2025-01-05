<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

abstract class DataNode extends Node
{
    public static bool $enablePeepholeOptimization = true;

    abstract public function infer(): Type;

    public function peephole(): self
    {
        if ($this instanceof ConstantNode || !self::$enablePeepholeOptimization) {
            return $this;
        }

        $type = $this->infer();
        if ($type instanceof ConstantType) {
            $new = (new ConstantNode($type->value))->peephole();
            $this->dce($new);
            return $new;
        }

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

    abstract public function print(): string;
}
