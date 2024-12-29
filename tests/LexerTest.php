<?php

use SimplePhp\IntegerToken;
use SimplePhp\Lexer;
use SimplePhp\Token;
use SimplePhp\TokenKind;

test('lexer', function () {
    expect((new Lexer("return 42;"))->collect())->toEqual([
        new Token(TokenKind::Return_),
        new IntegerToken(42),
        new Token(TokenKind::Semicolon),
        new Token(TokenKind::Eof),
    ]);
});
