<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Ir\DataNode;
use SimplePhp\Syntax\Parser;

describe('variable', function () {
    test('decl', function () {
        $mermaid = new Mermaid();
        $node = (new Parser('var a = 1; return a;'))->parse();
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
    });

    test('add', function () {
        $mermaid = new Mermaid();
        $node = (new Parser('var a = 1; var b = 2; return a + b;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 3]
          end
          subgraph Control
            0[Start]
            2[Return]
          end
          0 --> 2
          1 --> 2
        MERMAID);
    });

    test('scope', function () {
        $mermaid = new Mermaid();
        $node = (new Parser('var a = 1; var b = 2; var c = 0; { var b = 3; c = a + b; } return c;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 4]
          end
          subgraph Control
            0[Start]
            2[Return]
          end
          0 --> 2
          1 --> 2
        MERMAID);
    });

    test('scope no peephole', function () {
        $mermaid = new Mermaid();
        DataNode::$enablePeepholeOptimization = false;
        $node = (new Parser('var a = 1; var b = 2; var c = 0; { var b = 3; c = a + b; } return c;'))->parse();
        DataNode::$enablePeepholeOptimization = true;
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
            2[Constant 3]
            4[Add]
          end
          subgraph Control
            0[Start]
            3[Return]
          end
          0 --> 3
          4 --> 3
          1 --> 4
          2 --> 4
        MERMAID);
    });

    test('dist', function () {
        $mermaid = new Mermaid();
        $node = (new Parser(<<<CODE
        var x0 = 1;
        var y0 = 2;
        var x1 = 3;
        var y1 = 4;
        return (x0 - x1) * (x0 - x1) + (y0 - y1) * (y0 - y1);
        CODE))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 8]
          end
          subgraph Control
            0[Start]
            2[Return]
          end
          0 --> 2
          1 --> 2
        MERMAID);
    });

    test('self assign', function () {
        (new Parser('var a = a; return a;'))->parse();
    })->throws(\Exception::class, 'Undeclared identifier a');

    test('unclosed scope', function () {
        (new Parser('var a = 1; var b = 2; var c = 0; { var b = 3; c = a + b;'))->parse();
    })->throws(\Exception::class, 'Unexpected token Eof, expected CurlyRight');

    test('nested scopes', function () {
        $mermaid = new Mermaid();
        $node = (new Parser('var a = 1; { var b = a; } return a;'))->parse();
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
    });
});
