<?php

namespace SimplePhp;

enum TokenKind
{
    case Eof;
    case Integer;
    case Return_;
    case Semicolon;
    case Whitespace;
}
