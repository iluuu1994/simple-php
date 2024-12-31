<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;
use SimplePhp\Syntax\Parser;

abstract class DataNode extends Node
{
    public static bool $enablePeepholeOptimization = true;

    public abstract function infer(): Type;

    public function peephole(): self
    {
        if ($this instanceof ConstantNode || !self::$enablePeepholeOptimization) {
            return $this;
        }

        $type = $this->infer();

        if ($type instanceof ConstantType) {
            $this->kill();
            return (new ConstantNode(Parser::getStart(), $type->value))->peephole();
        }

        return $this;
    }
}
