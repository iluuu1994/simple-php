<?php

use SimplePhp\Graph\Code;
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

    test('a - 1 to a + (-1)', function () {
        $code = new Code();

        $node = (new Parser('return arg - 1 + 2;'))->parse();
        expect($code->print($node))->toBe('return (arg + 1);');
    });
});
