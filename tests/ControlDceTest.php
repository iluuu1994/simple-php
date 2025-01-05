<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

describe('control dce', function () {
    test('basic', function () {
        $mermaid = new Mermaid();
        $node = (new Parser('if (0) { return 0; } return 1;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
          end
          subgraph Control
            0[Start]
            2[Return]
          end
          0 & 1 --> 2
        MERMAID);
    });
});
