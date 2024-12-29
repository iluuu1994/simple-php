<?php

namespace SimplePhp;

class Token
{
    public function __construct(
        public TokenKind $kind,
    ) {}
}
