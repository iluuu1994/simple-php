<?php

use SimplePhp\Graph\Code;
use SimplePhp\Syntax\Parser;

describe('comparison', function () {
    $code = new Code();

    test('eq', function () use ($code) {
        expect($code->print((new Parser('return 3 == 3;'))->parse()))
          ->toBe('return 1;');
    });

    test('eq 2', function () use ($code) {
        expect($code->print((new Parser('return 3 == 4;'))->parse()))
          ->toBe('return 0;');
    });

    test('not eq', function () use ($code) {
        expect($code->print((new Parser('return 3 != 3;'))->parse()))
          ->toBe('return 0;');
    });

    test('not eq 2', function () use ($code) {
        expect($code->print((new Parser('return 3 != 4;'))->parse()))
          ->toBe('return 1;');
    });
});
