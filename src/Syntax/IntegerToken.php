<?php

namespace SimplePhp\Syntax;

class IntegerToken extends Token
{
    public function __construct(
        public int $value,
    ) {
        parent::__construct(TokenKind::Integer);
    }
}
