<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

test('peephole', function () {
    $mermaid = new Mermaid();

    $node = (new Parser('return 1 + 2 * 3 - 4 / -2;'))->parse();
    expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
    graph TD
      subgraph Data
        15[Constant 9]
      end
      subgraph Control
        0[Start]
        16[Return]
      end
      0 --> 16
      15 --> 16
    MERMAID);
});
