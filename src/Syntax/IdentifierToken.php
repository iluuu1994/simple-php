<?php

namespace SimplePhp\Syntax;

class IdentifierToken extends Token
{
    public function __construct(
        public string $name,
    ) {
        parent::__construct(TokenKind::Identifier);
    }
}
