<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\BotType;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Inference\Type;

class PhiNode extends DataNode
{
    /** @param list<DataNode> $dataNodes */
    public function __construct(
        public MergeNode $mergeNode,
        array $dataNodes,
    ) {
        parent::__construct([$mergeNode, ...$dataNodes]);
    }

    public function infer(): Type
    {
        return new BotType();
    }

    public function __toString(): string
    {
        return 'Phi';
    }

    public function print(): string
    {
        throw new \Exception('Unsupported');
    }
}
