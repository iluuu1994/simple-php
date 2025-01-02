<?php

namespace SimplePhp\Graph;

use SimplePhp\Ir\DataNode;
use SimplePhp\Ir\ReturnNode;
use SimplePhp\Ir\StartNode;

class Code
{
    public function print(StartNode $node): string
    {
        // FIXME: This will obviously need to be recursive once we can have a proper DFG.
        $result = '';
        foreach ($node->outputs as $output) {
            if ($output instanceof ReturnNode) {
                $expr = $output->inputs[1];
                assert($expr instanceof DataNode);
                $result .= 'return ' . $expr->print() . ';';
            }
        }
        return $result;
    }
}
