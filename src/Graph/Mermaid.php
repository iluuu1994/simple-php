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
            $node = array_shift($worklist);

            if ($node instanceof DataNode) {
                $dataNodes .= '    ' . $node->id . '[' . $node . "]\n";
            } else {
                assert($node instanceof ControlNode);
                $controlNodes .= '    ' . $node->id . '[' . $node . "]\n";
            }

            foreach ($node->outputs as $output) {
                if (!isset($visited[$output])) {
                    $visited[$output] = true;
                    $worklist[] = $output;
                }
            }

            /* For edges, iterate inputs rather than outputs to maintain input order. */
            foreach ($node->inputs as $input) {
                /* Skip fake data edges. */
                if ($input && !($input instanceof StartNode && $node instanceof DataNode)) {
                    $edges .= '  ' . $input->id . ' --> ' . $node->id . "\n";
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
