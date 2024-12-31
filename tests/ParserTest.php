<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Ir\DataNode;
use SimplePhp\Syntax\Parser;

beforeAll(function () {
    DataNode::$enablePeepholeOptimization = false;
});

afterAll(function () {
    DataNode::$enablePeepholeOptimization = true;
});

describe('parser', function () {
    $mermaid = new Mermaid();

    test('basic', function () use ($mermaid) {
        $node = (new Parser('{ return 42; }'))->parse();
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

    test('sum', function () use ($mermaid) {
        $node = (new Parser('{ return 1 + 2; }'))->parse();
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
    });

    test('parens', function () use ($mermaid) {
        $node = (new Parser('{ return (1); }'))->parse();
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

    test('left-associative arithmetics', function () use ($mermaid) {
        $node = (new Parser('{ return 1 - 2 - 3; }'))->parse();
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
    });

    test('right-associative with parens', function () use ($mermaid) {
        $node = (new Parser('{ return 1 - (2 - 3); }'))->parse();
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

    test('complex', function () use ($mermaid) {
        $node = (new Parser('{ return 1 + 2 * 3 - 4 / -5; }'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
            2[Constant 2]
            3[Constant 3]
            6[Constant 4]
            7[Constant 5]
            5[Add]
            4[Mul]
            9[Div]
            8[Neg]
            10[Sub]
          end
          subgraph Control
            0[Start]
            11[Return]
          end
          0 --> 11
          10 --> 11
          1 --> 5
          4 --> 5
          2 --> 4
          3 --> 4
          6 --> 9
          8 --> 9
          7 --> 8
          5 --> 10
          9 --> 10
        MERMAID);
    });
});
