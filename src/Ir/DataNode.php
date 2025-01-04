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
            $this->kill();
            return (new ConstantNode($type->value))->peephole();
        }

        $idealized = $this->idealize();
        if ($idealized !== null) {
            $this->kill();
            return $idealized->peephole();
        }

        return $this;
    }

    public function idealize(): ?self
    {
        return null;
    }

    abstract public function print(): string;
}
