<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

test('parser', function () {
    $mermaid = new Mermaid();

    $node = (new Parser('return 42;'))->parse();
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

    $node = (new Parser('return 1 + 2;'))->parse();
    expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
    graph TD
      subgraph Data
        4[Constant 1]
        5[Constant 2]
        6[Add]
      end
      subgraph Control
        3[Start]
        7[Return]
      end
      3 --> 7
      4 --> 6
      5 --> 6
      6 --> 7
    MERMAID);
});
