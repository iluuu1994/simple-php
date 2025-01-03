<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

describe('if', function () {
    test('return variations', function () {
        $test = function (string $code) {
            $mermaid = new Mermaid();
            $node = (new Parser($code))->parse();
            return $mermaid->buildGraph($node);
        };
        expect($test(<<<CODE
        var x = 1;
        var y = 2;
        if (arg) {
            return x;
        } else {
            return y;
        }
        CODE))
        ->toBe($test(<<<CODE
        var x = 1;
        var y = 2;
        if (arg) {
            return x;
        }
        return y;
        CODE))
        ->toBe($test(<<<CODE
        var x = 1;
        var y = 2;
        if (arg) {
        } else {
            return y;
        }
        return x;
        CODE))
        ->toBe($test(<<<CODE
        var x = 1;
        var y = 2;
        if (arg) {
            return x;
        } else {
        }
        return y;
        CODE))
        ->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Arg]
            2[Constant 1]
            3[Constant 2]
          end
          subgraph Control
            0[Start]
            4[If]
            5[Return]
            7[Return]
            6[True]
            8[False]
          end
          0 --> 4
          1 --> 4
          6 --> 5
          2 --> 5
          8 --> 7
          3 --> 7
          4 --> 6
          4 --> 8
        MERMAID);
    });

    test('phi', function () {
        $mermaid = new Mermaid();
        $node = (new Parser(<<<CODE
        var x = 1;
        var y = 2;
        if (arg) {
            y = 3;
        } else {
            x = 4;
        }
        return x + y;
        CODE))->parse();
        expect($mermaid->buildGraph($node))
        ->toBe(<<<MERMAID
        graph TD
          subgraph Data
            1[Arg]
            2[Constant 1]
            3[Constant 2]
            5[Constant 3]
            6[Constant 4]
            7[Phi]
            9[Phi]
            12[Add]
          end
          subgraph Control
            0[Start]
            4[If]
            10[True]
            11[False]
            8[Merge]
            13[Return]
          end
          0 --> 4
          1 --> 4
          8 --> 7
          2 --> 7
          6 --> 7
          8 --> 9
          5 --> 9
          3 --> 9
          4 --> 10
          4 --> 11
          7 --> 12
          9 --> 12
          10 --> 8
          11 --> 8
          8 --> 13
          12 --> 13
        MERMAID);
    });
});
