<?php

namespace SimplePhp\Syntax;

enum TokenKind
{
    case Eof;
    case Identifier;
    case Integer;
    case Return_;
    case Semicolon;
    case Whitespace;
}
