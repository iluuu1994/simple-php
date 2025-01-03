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
          0 & 1 --> 2
        MERMAID);
    });

    test('sum', function () use ($mermaid) {
        $node = (new Parser('return 1 + 2;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
            2[Constant 2]
            4[Add]
          end
          subgraph Control
            0[Start]
            3[Return]
          end
          0 & 4 --> 3
          1 & 2 --> 4
        MERMAID);
    });

    test('parens', function () use ($mermaid) {
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
          0 & 1 --> 2
        MERMAID);
    });

    test('left-associative arithmetics', function () use ($mermaid) {
        $node = (new Parser('return 1 - 2 - 3;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
            2[Constant 2]
            3[Constant 3]
            6[Sub]
            5[Sub]
          end
          subgraph Control
            0[Start]
            4[Return]
          end
          0 & 5 --> 4
          1 & 2 --> 6
          6 & 3 --> 5
        MERMAID);
    });

    test('right-associative with parens', function () use ($mermaid) {
        $node = (new Parser('return 1 - (2 - 3);'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
            2[Constant 2]
            3[Constant 3]
            5[Sub]
            6[Sub]
          end
          subgraph Control
            0[Start]
            4[Return]
          end
          0 & 5 --> 4
          1 & 6 --> 5
          2 & 3 --> 6
        MERMAID);
    });

    test('complex', function () use ($mermaid) {
        $node = (new Parser('return 1 + 2 * 3 - 4 / -5;'))->parse();
        expect($mermaid->buildGraph($node))->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Constant 1]
            2[Constant 2]
            3[Constant 3]
            4[Constant 4]
            5[Constant 5]
            8[Add]
            9[Mul]
            10[Div]
            11[Neg]
            7[Sub]
          end
          subgraph Control
            0[Start]
            6[Return]
          end
          0 & 7 --> 6
          1 & 9 --> 8
          2 & 3 --> 9
          4 & 11 --> 10
          5 --> 11
          8 & 10 --> 7
        MERMAID);
    });
});
