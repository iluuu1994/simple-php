<?php

namespace SimplePhp\Syntax;

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
        $result = $this->lexKeywords()
            ?? $this->lexInteger()
            ?? $this->lexSymbols()
            ?? $this->lexIdentifier()
            ?? throw new \Exception('Unexpected character ' . $this->code[$this->position]);

        return $result;
    }

    private function skipWhitespace(): void
    {
        while ($this->position < strlen($this->code) && ctype_space($this->code[$this->position])) {
            $this->position++;
        }
    }

    private function lexKeywords(): ?Token
    {
        $keywords = [
            ['else', TokenKind::Else],
            ['if', TokenKind::If],
            ['return', TokenKind::Return_],
            ['var', TokenKind::Var],
        ];

        foreach ($keywords as [$string, $kind]) {
            if (substr_compare($this->code, $string, $this->position, strlen($string)) === 0
             /* Keywords must end with a non-identifier character. */
             && ($this->position + strlen($string) >= strlen($this->code)
              || !$this->isIdentifierChar($this->code[$this->position + strlen($string)]))) {
                $this->position += strlen($string);
                return new Token($kind);
            }
        }

        return null;
    }

    private function lexInteger(): ?Token
    {
        $char = $this->code[$this->position];
        if (!($char >= '0' && $char <= '9')) {
            return null;
        }
        $start = $this->position;
        $this->position++;
        while ($this->position < strlen($this->code)) {
            $char = $this->code[$this->position];
            if (!($char >= '0' && $char <= '9')) {
                break;
            }
            $this->position++;
        }
        return new IntegerToken((int) substr($this->code, $start, $this->position - $start));
    }

    private function lexSymbols(): ?Token
    {
        $char = $this->code[$this->position++];
        switch ($char) {
            case ';':
                $kind = TokenKind::Semicolon;
                break;
            case '+':
                $kind = TokenKind::Plus;
                break;
            case '-':
                $kind = TokenKind::Minus;
                break;
            case '/':
                $kind = TokenKind::Slash;
                break;
            case '*':
                $kind = TokenKind::Asterisk;
                break;
            case '(':
                $kind = TokenKind::ParenLeft;
                break;
            case ')':
                $kind = TokenKind::ParenRight;
                break;
            case '{':
                $kind = TokenKind::CurlyLeft;
                break;
            case '}':
                $kind = TokenKind::CurlyRight;
                break;
            case '=':
                if ($this->position < strlen($this->code) && $this->code[$this->position] === '=') {
                    $kind = TokenKind::EqualsEquals;
                    $this->position++;
                } else {
                    $kind = TokenKind::Equals;
                }
                break;
            case '<':
                if ($this->position < strlen($this->code) && $this->code[$this->position] === '=') {
                    $kind = TokenKind::AngleLeftEquals;
                    $this->position++;
                } else {
                    $kind = TokenKind::AngleLeft;
                }
                break;
            case '>':
                if ($this->position < strlen($this->code) && $this->code[$this->position] === '=') {
                    $kind = TokenKind::AngleRightEquals;
                    $this->position++;
                } else {
                    $kind = TokenKind::AngleRight;
                }
                break;
            case '!':
                if ($this->position < strlen($this->code) && $this->code[$this->position] === '=') {
                    $kind = TokenKind::ExclamationMarkEquals;
                    $this->position++;
                } else {
                    $this->position--;
                    return null;
                }
                break;
            default:
                $this->position--;
                return null;
        }
        return new Token($kind);
    }

    private function lexIdentifier(): ?Token
    {
        if (!$this->isIdentifierChar($this->code[$this->position])) {
            return null;
        }
        $start = $this->position++;
        while ($this->position < strlen($this->code) && $this->isIdentifierChar($this->code[$this->position])) {
            $this->position++;
        }
        return new IdentifierToken(substr($this->code, $start, $this->position - $start));
    }

    private function isIdentifierChar(string $char): bool
    {
        $lower = strtolower($char);
        return ($lower >= 'a' && $lower <= 'z')
            || ($lower >= '0' && $lower <= '9')
            || $lower == '_';
    }
}
