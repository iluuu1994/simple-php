<?php

use SimplePhp\Syntax\IdentifierToken;
use SimplePhp\Syntax\IntegerToken;
use SimplePhp\Syntax\Lexer;
use SimplePhp\Syntax\Token;
use SimplePhp\Syntax\TokenKind;

describe('lexer', function () {
    test('basic', function () {
        expect((new Lexer('return 42;'))->collect())->toEqual([
            new Token(TokenKind::Return_),
            new IntegerToken(42),
            new Token(TokenKind::Semicolon),
            new Token(TokenKind::Eof),
        ]);
    });

    test('numbers', function () {
        expect((new Lexer('0 1 20 42 9999'))->collect())->toEqual([
            new IntegerToken(0),
            new IntegerToken(1),
            new IntegerToken(20),
            new IntegerToken(42),
            new IntegerToken(9999),
            new Token(TokenKind::Eof),
        ]);
    });

    test('identifiers starting with keyword', function () {
        expect((new Lexer('returns'))->collect())->toEqual([
            new IdentifierToken('returns'),
            new Token(TokenKind::Eof),
        ]);
    });

    test('sum', function () {
        expect((new Lexer('1 + 2'))->collect())->toEqual([
            new IntegerToken(1),
            new Token(TokenKind::Plus),
            new IntegerToken(2),
            new Token(TokenKind::Eof),
        ]);
    });

    test('parens', function () {
        expect((new Lexer('(1);'))->collect())->toEqual([
            new Token(TokenKind::ParenLeft),
            new IntegerToken(1),
            new Token(TokenKind::ParenRight),
            new Token(TokenKind::Semicolon),
            new Token(TokenKind::Eof),
        ]);
    });

    test('arithmetics', function () {
        expect((new Lexer('1 - (2 - 3);'))->collect())->toEqual([
            new IntegerToken(1),
            new Token(TokenKind::Minus),
            new Token(TokenKind::ParenLeft),
            new IntegerToken(2),
            new Token(TokenKind::Minus),
            new IntegerToken(3),
            new Token(TokenKind::ParenRight),
            new Token(TokenKind::Semicolon),
            new Token(TokenKind::Eof),
        ]);
    });
});
