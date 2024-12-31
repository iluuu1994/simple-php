<?php

namespace SimplePhp\Syntax;

use SimplePhp\Ir\AddNode;
use SimplePhp\Ir\ConstantNode;
use SimplePhp\Ir\ControlNode;
use SimplePhp\Ir\DataNode;
use SimplePhp\Ir\DivNode;
use SimplePhp\Ir\MulNode;
use SimplePhp\Ir\Node;
use SimplePhp\Ir\ReturnNode;
use SimplePhp\Ir\StartNode;
use SimplePhp\Ir\SubNode;
use SimplePhp\UnexpectedError;

class Parser
{
    private Lexer $lexer;
    private ?StartNode $start = null;
    private ?ControlNode $ctrl = null;

    public function __construct(string $code)
    {
        Node::resetIds();
        $this->lexer = new Lexer($code);
    }

    public function parse(): StartNode
    {
        $start = new StartNode();
        $this->start = $start;
        $this->ctrl = $this->start;
        $node = $this->parseProgram();
        $this->consume(TokenKind::Eof);
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
        $lhs = $this->parseMulDiv();
        while (true) {
            $current = $this->lexer->current();
            if ($current->kind === TokenKind::Plus) {
                $this->lexer->next();
                $lhs = new AddNode($lhs, $this->parseMulDiv());
            } else if ($current->kind === TokenKind::Minus) {
                $this->lexer->next();
                $lhs = new SubNode($lhs, $this->parseMulDiv());
            } else {
                break;
            }
        }
        return $lhs;
    }

    private function parseMulDiv(): DataNode
    {
        $lhs = $this->parseTerm();
        while (true) {
            $current = $this->lexer->current();
            if ($current->kind === TokenKind::Asterisk) {
                $this->lexer->next();
                $lhs = new MulNode($lhs, $this->parseTerm());
            } else if ($current->kind === TokenKind::Slash) {
                $this->lexer->next();
                $lhs = new DivNode($lhs, $this->parseTerm());
            } else {
                break;
            }
        }
        return $lhs;
    }

    private function parseTerm(): DataNode
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Integer) {
            assert($current instanceof IntegerToken);
            $this->lexer->next();
            return new ConstantNode($this->start ?? throw new UnexpectedError(), $current->value);
        } else if ($current->kind === TokenKind::ParenLeft) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::ParenRight);
            return $expr;
        } else {
            $this->unexpectedToken();
        }
    }

    private function consume(TokenKind $kind): void
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
