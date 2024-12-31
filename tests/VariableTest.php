<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

describe('variable', function () {
    test('basic', function () {
        $mermaid = new Mermaid();

        $node = (new Parser('{ var x = 1; var y = 2; return x + y; }'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            4[Constant 3]
          end
          subgraph Control
            0[Start]
            5[Return]
          end
          0 --> 5
          4 --> 5
        MERMAID);
    });
});
