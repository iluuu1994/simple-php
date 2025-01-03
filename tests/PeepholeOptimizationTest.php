<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

describe('peephole', function () {
    test('arithmetics', function () {
        $mermaid = new Mermaid();

        $node = (new Parser('return 1 + 2 * 3 - 4 / -2;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 9]
          end
          subgraph Control
            0[Start]
            2[Return]
          end
          0 & 1 --> 2
        MERMAID);
    });
});
