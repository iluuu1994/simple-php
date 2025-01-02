<?php

use SimplePhp\Graph\Code;
use SimplePhp\Inference\ConstantType;
use SimplePhp\Syntax\Parser;

describe('arg', function () {
    $code = new Code();

    test('adds are pulled to the left', function () use ($code) {
        expect($code->print((new Parser('return (arg + 1) + (arg + 1);'))->parse()))
          ->toBe('return ((arg * 2) + 2);');
    });

    test('peephole', function () use ($code) {
        expect($code->print((new Parser('return 1 + arg + 2;'))->parse()))
          ->toBe('return (arg + 3);');
    });

    test('peephole 2', function () use ($code) {
        expect($code->print((new Parser('return 1 + (arg + 2);'))->parse()))
          ->toBe('return (arg + 3);');
    });

    test('add 0', function () use ($code) {
        expect($code->print((new Parser('return 0 + arg;'))->parse()))
          ->toBe('return arg;');
    });

    test('add add mul', function () use ($code) {
        expect($code->print((new Parser('return arg + 0 + arg;'))->parse()))
          ->toBe('return (arg * 2);');
    });

    test('peephole 3', function () use ($code) {
        expect($code->print((new Parser('return 1 + arg + 2 + arg + 3;'))->parse()))
          ->toBe('return ((arg * 2) + 6);');
    });

    test('mul 1', function () use ($code) {
        expect($code->print((new Parser('return 1 * arg;'))->parse()))
          ->toBe('return arg;');
    });

    test('constant arg', function () use ($code) {
        expect($code->print((new Parser('return arg;'))->parse(new ConstantType(2))))
          ->toBe('return 2;');
    });

    test('bug 1', function () use ($code) {
        expect($code->print((new Parser('var a = arg + 1; var b = a; b = 1; return a + 2;'))->parse()))
          ->toBe('return (arg + 3);');
    });

    test('bug 2', function () use ($code) {
        expect($code->print((new Parser('var a = arg + 1; a = a; return a;'))->parse()))
          ->toBe('return (arg + 1);');
    });

    test('bug 3', function () {
        (new Parser('vara=1; return a;'))->parse();
    })->throws('Undeclared identifier vara');

    test('bug 4', function () use ($code) {
        expect($code->print((new Parser('return -arg;'))->parse()))
          ->toBe('return -arg;');
    });
});
