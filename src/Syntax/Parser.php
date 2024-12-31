<?php

namespace SimplePhp\Syntax;

use SimplePhp\Ir\AddNode;
use SimplePhp\Ir\ConstantNode;
use SimplePhp\Ir\ControlNode;
use SimplePhp\Ir\DataNode;
use SimplePhp\Ir\Node;
use SimplePhp\Ir\ReturnNode;
use SimplePhp\Ir\StartNode;
use SimplePhp\UnexpectedError;

class Parser
{
    private Lexer $lexer;
    private ?StartNode $start = null;
    private ?ControlNode $ctrl = null;

    public function __construct(string $code)
    {
        $this->lexer = new Lexer($code);
    }

    public function parse(): StartNode
    {
        $start = new StartNode();
        $this->start = $start;
        $this->ctrl = $this->start;
        $node = $this->parseProgram();
        $this->expectTokenKind(TokenKind::Eof);
        return $start;
    }

    private function parseProgram(): Node
    {
        return $this->parseStatement();
    }

    private function parseStatement(): Node
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Return_) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::Semicolon);
            $result = new ReturnNode($this->getCtrl(), $expr);
            $this->ctrl = null;
            return $result;
        } else {
            $this->unexpectedToken();
        }
    }

    private function parseExpression(): DataNode
    {
        return $this->parseAddSub();
    }

    private function parseAddSub(): DataNode
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

    private function parseTerm(): DataNode
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Integer) {
            assert($current instanceof IntegerToken);
            $this->lexer->next();
            return new ConstantNode($this->start ?? throw new UnexpectedError(), $current->value);
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

    private function unexpectedToken(): never
    {
        $current = $this->lexer->current();
        throw new \Exception('Unexpected token ' . $current->kind->name);
    }

    private function getCtrl(): ControlNode
    {
        return $this->ctrl ?? throw new UnexpectedError('No ctrl node');
    }
}
