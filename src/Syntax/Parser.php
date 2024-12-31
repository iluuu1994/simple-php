<?php

namespace SimplePhp\Syntax;

use SimplePhp\Ir\AddNode;
use SimplePhp\Ir\ConstantNode;
use SimplePhp\Ir\ControlNode;
use SimplePhp\Ir\Node;
use SimplePhp\Ir\ReturnNode;
use SimplePhp\Ir\StartNode;

class Parser
{
    private Lexer $lexer;
    private ?StartNode $start = null;
    private ?ControlNode $ctrl = null;

    public function __construct(string $code)
    {
        $this->lexer = new Lexer($code);
    }

    public function parse(): Node
    {
        $this->start = new StartNode();
        $this->ctrl = $this->start;
        $node = $this->parseProgram();
        $this->expectTokenKind(TokenKind::Eof);
        return $this->start;
    }

    private function parseProgram(): Node
    {
        $node = $this->parseStatement();
        if ($node === null) {
            throw new \Exception('Expected statement');
        }
        return $node;
    }

    private function parseStatement(): Node
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Return_) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::Semicolon);
            $result = new ReturnNode($this->ctrl, $expr);
            $this->ctrl = null;
            return $result;
        } else {
            $this->unexpectedToken();
        }
    }

    private function parseExpression(): Node
    {
        return $this->parseAddSub();
    }

    private function parseAddSub(): Node
    {
        $term = $this->parseTerm();
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Plus) {
            $this->lexer->next();
            return new AddNode($term, $this->parseTerm());
        } else if ($current->kind === TokenKind::Minus) {
            throw new \Exception('Unimplemented');
        } else {
            return $term;
        }
    }

    private function parseTerm(): Node
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Integer) {
            assert($current instanceof IntegerToken);
            $this->lexer->next();
            return new ConstantNode($this->start, $current->value);
        } else {
            $this->unexpectedToken();
        }
    }

    private function consume(TokenKind $kind): void
    {
        $this->expectTokenKind($kind);
        $this->lexer->next();
    }

    private function expectTokenKind(TokenKind $kind): void
    {
        $current = $this->lexer->current();
        if ($current->kind !== $kind) {
            throw new \Exception('Unexpected token ' . $current->kind->name . ', expected ' . $kind->name);
        }
        $this->lexer->next();
    }

    private function unexpectedToken(): void
    {
        $current = $this->lexer->current();
        throw new \Exception('Unexpected token ' . $current->kind->name);
    }
}
