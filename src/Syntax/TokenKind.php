<?php

namespace SimplePhp\Syntax;

enum TokenKind
{
    case Asterisk;
    case Eof;
    case Identifier;
    case Integer;
    case Minus;
    case ParenLeft;
    case ParenRight;
    case Plus;
    case Return_;
    case Semicolon;
    case Slash;
    case Whitespace;
}
