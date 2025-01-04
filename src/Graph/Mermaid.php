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
        $edgesMap = [];

        $idCounter = 0;
        $ids = [];

        while (count($worklist)) {
            $node = array_shift($worklist);

            $id = $ids[$node->id] ?? ($ids[$node->id] = $idCounter++);

            if ($node instanceof DataNode) {
                $dataNodes .= '    ' . $id . '[' . $node . "]\n";
            } else {
                assert($node instanceof ControlNode);
                $controlNodes .= '    ' . $id . '[' . $node . "]\n";
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
                if (!($input instanceof StartNode && $node instanceof DataNode)) {
                    $inputId = $ids[$input->id] ?? ($ids[$input->id] = $idCounter++);
                    $edgesMap[$id][] = $inputId;
                }
            }
        }

        $edges = '';
        foreach ($edgesMap as $to => $from) {
            $edges .= '  ' . implode(' & ', $from) . ' --> ' . $to . "\n";
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
