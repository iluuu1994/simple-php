<?php

namespace SimplePhp\Syntax;

class Token
{
    public function __construct(
        public TokenKind $kind,
    ) {}
}
