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
        1[Constant 1]
        2[Constant 2]
        3[Add]
      end
      subgraph Control
        0[Start]
        4[Return]
      end
      0 --> 4
      3 --> 4
      1 --> 3
      2 --> 3
    MERMAID);

    $node = (new Parser('return (1);'))->parse();
    expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
    graph TD
      subgraph Data
        1[Constant 1]
      end
      subgraph Control
        0[Start]
        2[Return]
      end
      0 --> 2
      1 --> 2
    MERMAID);

    $node = (new Parser('return 1 - 2 - 3;'))->parse();
    expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
    graph TD
      subgraph Data
        1[Constant 1]
        2[Constant 2]
        4[Constant 3]
        3[Sub]
        5[Sub]
      end
      subgraph Control
        0[Start]
        6[Return]
      end
      0 --> 6
      5 --> 6
      1 --> 3
      2 --> 3
      3 --> 5
      4 --> 5
    MERMAID);

    $node = (new Parser('return 1 - (2 - 3);'))->parse();
    expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
    graph TD
      subgraph Data
        1[Constant 1]
        2[Constant 2]
        3[Constant 3]
        5[Sub]
        4[Sub]
      end
      subgraph Control
        0[Start]
        6[Return]
      end
      0 --> 6
      5 --> 6
      1 --> 5
      4 --> 5
      2 --> 4
      3 --> 4
    MERMAID);
});
