<?php

namespace SimplePhp\Graph;

use SimplePhp\Ir\ControlNode;
use SimplePhp\Ir\DataNode;
use SimplePhp\Ir\StartNode;

class Mermaid
{
    public function buildGraph(StartNode $node): string
    {
        $worklist = [$node];
        $visited = new \WeakMap();

        $dataNodes = '';
        $controlNodes = '';
        $edges = '';

        while (count($worklist)) {
            $node = array_pop($worklist);

            if ($node instanceof DataNode) {
                $dataNodes .= '    ' . $node->id . '[' . $node . "]\n";
            } else {
                assert($node instanceof ControlNode);
                $controlNodes .= '    ' . $node->id . '[' . $node . "]\n";
            }

            foreach ($node->outputs as $output) {
                /* Skip fake data edges. */
                if (!($node instanceof StartNode && $output instanceof DataNode)) {
                    $edges .= '  ' . $node->id . ' --> ' . $output->id . "\n";
                }

                if (!isset($visited[$output])) {
                    $visited[$output] = true;
                    $worklist[] = $output;
                }
            }
        }

        $dataNodes = trim($dataNodes);
        $controlNodes = trim($controlNodes);
        $edges = trim($edges);

        return <<<MERMAID
        graph TD
          subgraph Data
            $dataNodes
          end
          subgraph Control
            $controlNodes
          end
          $edges
        MERMAID;
    }
}
