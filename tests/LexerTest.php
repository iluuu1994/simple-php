<?php

use SimplePhp\Syntax\IdentifierToken;
use SimplePhp\Syntax\IntegerToken;
use SimplePhp\Syntax\Lexer;
use SimplePhp\Syntax\Token;
use SimplePhp\Syntax\TokenKind;

test('lexer', function () {
    expect((new Lexer('return 42;'))->collect())->toEqual([
        new Token(TokenKind::Return_),
        new IntegerToken(42),
        new Token(TokenKind::Semicolon),
        new Token(TokenKind::Eof),
    ]);

    expect((new Lexer('0 1 20 42 9999'))->collect())->toEqual([
        new IntegerToken(0),
        new IntegerToken(1),
        new IntegerToken(20),
        new IntegerToken(42),
        new IntegerToken(9999),
        new Token(TokenKind::Eof),
    ]);

    expect((new Lexer('returns'))->collect())->toEqual([
        new IdentifierToken('returns'),
        new Token(TokenKind::Eof),
    ]);
});
