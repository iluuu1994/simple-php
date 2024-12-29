<?php

namespace SimplePhp;

class Lexer
{
    private string $code;
    private int $position = 0;
    private ?Token $current = null;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function current(): Token
    {
        return $this->current ??= $this->lex();
    }

    public function next(): Token
    {
        return $this->current = $this->lex();
    }

    /** @return list<Token> */
    public function collect(): array
    {
        $result = [];
        while (true) {
            $token = $this->next();
            $result[] = $token;
            if ($token->kind === TokenKind::Eof) {
                break;
            }
        }
        return $result;
    }

    private function lex(): Token
    {
        $this->skipWhitespace();
        if ($this->position >= strlen($this->code)) {
            return new Token(TokenKind::Eof);
        }
        return $this->lexKeywords()
            ?? $this->lexInteger()
            ?? $this->lexSymbols()
            ?? throw new \Exception('Unexpected character ' . $this->code[$this->position]);
    }

    private function skipWhitespace(): void
    {
        while ($this->position < strlen($this->code) && ctype_space($this->code[$this->position])) {
            $this->position++;
        }
    }

    private function lexKeywords(): ?Token
    {
        if (substr_compare($this->code, "return", $this->position, strlen("return")) === 0) {
            $this->position += strlen("return");
            return new Token(TokenKind::Return_);
        }
        return null;
    }

    private function lexInteger(): ?Token
    {
        $char = $this->code[$this->position];
        if (!($char >= '1' && $char <= '9')) {
            return null;
        }
        $start = $this->position;
        $this->position++;
        while (true) {
            $char = $this->code[$this->position];
            if (!($char >= '1' && $char <= '9')) {
                break;
            }
            $this->position++;
        }
        return new IntegerToken((int) substr($this->code, $start, $this->position - $start));
    }

    private function lexSymbols(): ?Token
    {
        $char = $this->code[$this->position];
        if ($char === ';') {
            $this->position++;
            return new Token(TokenKind::Semicolon);
        }
        return null;
    }
}
