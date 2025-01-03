<?php

use SimplePhp\Graph\Mermaid;
use SimplePhp\Syntax\Parser;

describe('variable', function () {
    test('if return variations', function () {
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
});
