<?php

namespace SimplePhp\Syntax;

use SimplePhp\Ir\AddNode;
use SimplePhp\Ir\ConstantNode;
use SimplePhp\Ir\ControlNode;
use SimplePhp\Ir\DataNode;
use SimplePhp\Ir\DivNode;
use SimplePhp\Ir\MulNode;
use SimplePhp\Ir\NegNode;
use SimplePhp\Ir\Node;
use SimplePhp\Ir\ReturnNode;
use SimplePhp\Ir\StartNode;
use SimplePhp\Ir\SubNode;
use SimplePhp\UnexpectedError;

class Parser
{
    public static ?StartNode $start = null;

    private Lexer $lexer;
    private ?ControlNode $ctrl = null;

    public function __construct(string $code)
    {
        Node::resetIds();
        $this->lexer = new Lexer($code);
    }

    public function parse(): StartNode
    {
        $start = new StartNode();
        self::$start = $start;
        $this->ctrl = $start;
        $this->parseProgram();
        $this->consume(TokenKind::Eof);
        return $start;
    }

    private function parseProgram(): void
    {
        $this->parseStatement();
    }

    private function parseStatement(): void
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Return_) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::Semicolon);
            $result = new ReturnNode($this->getCtrl(), $expr);
            $this->ctrl = null;
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
                $lhs = (new AddNode($lhs, $this->parseMulDiv()))->peephole();
            } else if ($current->kind === TokenKind::Minus) {
                $this->lexer->next();
                $lhs = (new SubNode($lhs, $this->parseMulDiv()))->peephole();
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
                $lhs = (new MulNode($lhs, $this->parseTerm()))->peephole();
            } else if ($current->kind === TokenKind::Slash) {
                $this->lexer->next();
                $lhs = (new DivNode($lhs, $this->parseTerm()))->peephole();
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
            return (new ConstantNode(self::$start ?? throw new UnexpectedError(), $current->value))->peephole();
        } else if ($current->kind === TokenKind::ParenLeft) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::ParenRight);
            return $expr;
        } else if ($current->kind === TokenKind::Minus) {
            $this->lexer->next();
            $expr = $this->parseTerm();
            return (new NegNode($expr))->peephole();
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

    public static function getStart(): StartNode
    {
        return self::$start ?? throw new UnexpectedError('No start node');
    }

    private function getCtrl(): ControlNode
    {
        return $this->ctrl ?? throw new UnexpectedError('No ctrl node');
    }
}
