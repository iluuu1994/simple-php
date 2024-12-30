<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

test('parser', function () {
    $node = (new Parser('return 42;'))->parse();
    $mermaid = new Mermaid();
    expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
    graph TD
      subgraph Data
        1[Constant 42]
      end
      subgraph Control
        0[Start]
        2[Return]
      end
      0 --> 2
      1 --> 2
    MERMAID);
});
