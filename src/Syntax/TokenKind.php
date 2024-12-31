<?php

namespace SimplePhp\Syntax;

enum TokenKind
{
    case Asterisk;
    case CurlyLeft;
    case CurlyRight;
    case Eof;
    case Equals;
    case Identifier;
    case Integer;
    case Minus;
    case ParenLeft;
    case ParenRight;
    case Plus;
    case Return_;
    case Semicolon;
    case Slash;
    case Var;
    case Whitespace;
}
