<?php

namespace SimplePhp\Syntax;

use SimplePhp\Inference\Type;
use SimplePhp\Ir\AddNode;
use SimplePhp\Ir\ArgNode;
use SimplePhp\Ir\BranchNode;
use SimplePhp\Ir\CompKind;
use SimplePhp\Ir\CompNode;
use SimplePhp\Ir\ConstantNode;
use SimplePhp\Ir\ControlNode;
use SimplePhp\Ir\DataNode;
use SimplePhp\Ir\DivNode;
use SimplePhp\Ir\IfNode;
use SimplePhp\Ir\MergeNode;
use SimplePhp\Ir\MulNode;
use SimplePhp\Ir\NegNode;
use SimplePhp\Ir\Node;
use SimplePhp\Ir\NotNode;
use SimplePhp\Ir\ReturnNode;
use SimplePhp\Ir\StartNode;
use SimplePhp\Ir\SubNode;
use SimplePhp\Ir\SymbolTable;
use SimplePhp\UnexpectedError;

class Parser
{
    public static ?StartNode $start = null;

    private Lexer $lexer;
    private SymbolTable $symbolTable;
    private ?ControlNode $ctrl = null;
    private ?DataNode $arg = null;

    public function __construct(string $code)
    {
        Node::resetIds();
        $this->lexer = new Lexer($code);
        $this->symbolTable = new SymbolTable();
    }

    public function parse(?Type $argType = null): StartNode
    {
        $start = new StartNode();
        self::$start = $start;
        $this->ctrl = $start;
        $this->arg = (new ArgNode($start, $argType))->peephole();
        $this->symbolTable->pushScope();
        $this->symbolTable->declare('arg', $this->arg);
        $this->parseProgram();
        $this->consume(TokenKind::Eof);
        $this->symbolTable->popScope();
        $this->symbolTable->kill();
        return $start;
    }

    private function parseProgram(): void
    {
        $this->parseBlock();
    }

    private function parseBlock(): void
    {
        $this->symbolTable->pushScope();
        while (true) {
            $current = $this->lexer->current();
            if ($current->kind === TokenKind::CurlyRight || $current->kind === TokenKind::Eof) {
                break;
            }
            $this->parseStatement();
        }
        $this->symbolTable->popScope();
    }

    private function parseStatement(): void
    {
        $current = $this->lexer->current();
        if ($current->kind === TokenKind::Return_) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::Semicolon);
            /* FIXME: This looks a bit weird, but the constructor links itself
             * into the graph. Can we make this API less confusing? */
            (new ReturnNode($this->getCtrl(), $expr))->peephole();
            $this->ctrl = null;
        } else if ($current->kind === TokenKind::CurlyLeft) {
            $this->consume(TokenKind::CurlyLeft);
            $this->parseBlock();
            $this->consume(TokenKind::CurlyRight);
        } else if ($current->kind === TokenKind::Var) {
            $this->parseVarDecl();
        } else if ($current->kind === TokenKind::If) {
            $this->parseIfElse();
        } else {
            $this->parseExpressionStatement();
        }
    }

    private function parseExpressionStatement(): void
    {
        $identifier = $this->consume(TokenKind::Identifier);
        assert($identifier instanceof IdentifierToken);
        $this->consume(TokenKind::Equals);
        $expr = $this->parseExpression();
        $this->consume(TokenKind::Semicolon);
        $this->symbolTable->update($identifier->name, $expr);
    }

    private function parseVarDecl(): void
    {
        $this->consume(TokenKind::Var);
        $identifier = $this->consume(TokenKind::Identifier);
        assert($identifier instanceof IdentifierToken);
        $this->consume(TokenKind::Equals);
        $expr = $this->parseExpression();
        $this->consume(TokenKind::Semicolon);

        $this->symbolTable->declare($identifier->name, $expr);
    }

    private function parseIfElse(): void
    {
        $this->consume(TokenKind::If);
        $this->consume(TokenKind::ParenLeft);
        $cond = $this->parseExpression();
        $this->consume(TokenKind::ParenRight);
        $origSymbolTable = clone $this->symbolTable;
        $if = (new IfNode($this->getCtrl(), $cond))->peephole();
        assert($if instanceof IfNode);

        /* Attach branches first to avoid DCE of the if node. */
        $tBranch = (new BranchNode($if, true));
        $fBranch = (new BranchNode($if, false));
        $tBranch = $tBranch->peephole();
        $fBranch = $fBranch->peephole();

        $this->ctrl = $tBranch;
        $this->parseStatement();
        $tCtrl = $this->ctrl;
        $tSymbolTable = $this->symbolTable;

        if ($this->lexer->current()->kind === TokenKind::Else) {
            $this->consume(TokenKind::Else);
            $this->ctrl = $fBranch;
            $this->symbolTable = $origSymbolTable;
            $this->parseStatement();
            $fCtrl = $this->ctrl;

            if ($tCtrl === null && $fCtrl === null) {
                /* Both branches returned, we're done. */
                $tSymbolTable->kill();
                $origSymbolTable->kill();
            } else if ($tCtrl === null) {
                $this->ctrl = $fCtrl;
                $this->symbolTable = $origSymbolTable;
                $tSymbolTable->kill();
            } else if ($fCtrl === null) {
                $this->ctrl = $tCtrl;
                $this->symbolTable = $tSymbolTable;
                $origSymbolTable->kill();
            } else {
                $merge = new MergeNode([$tCtrl, $fCtrl]);
                $this->ctrl = $merge;
                $this->symbolTable = SymbolTable::merged($tSymbolTable, $origSymbolTable, $merge);
                $tSymbolTable->kill();
                $origSymbolTable->kill();
            }
        } else {
            if ($tCtrl) {
                $merge = new MergeNode([$tCtrl, $fBranch]);
                $this->ctrl = $merge;
                $this->symbolTable = SymbolTable::merged($tSymbolTable, $origSymbolTable, $merge);
                $tSymbolTable->kill();
                $origSymbolTable->kill();
            } else {
                $this->ctrl = $fBranch;
                $this->symbolTable = $origSymbolTable;
                $tSymbolTable->kill();
            }
        }
    }

    private function parseExpression(): DataNode
    {
        return $this->parseComparison();
    }

    private function parseComparison(): DataNode
    {
        $lhs = $this->parseAddSub();
        while (true) {
            $current = $this->lexer->current();
            if ($current->kind === TokenKind::EqualsEquals) {
                $this->lexer->next();
                $lhs = (new CompNode(CompKind::Equal, $lhs, $this->parseAddSub()))->peephole();
            } else if ($current->kind === TokenKind::AngleLeft) {
                $this->lexer->next();
                $lhs = (new CompNode(CompKind::Lower, $lhs, $this->parseAddSub()))->peephole();
            } else if ($current->kind === TokenKind::AngleLeftEquals) {
                $this->lexer->next();
                $lhs = (new CompNode(CompKind::LowerEqual, $lhs, $this->parseAddSub()))->peephole();
            } else if ($current->kind === TokenKind::AngleRight) {
                $this->lexer->next();
                $lhs = (new CompNode(CompKind::Lower, $this->parseAddSub(), $lhs))->peephole();
            } else if ($current->kind === TokenKind::AngleRightEquals) {
                $this->lexer->next();
                $lhs = (new CompNode(CompKind::LowerEqual, $this->parseAddSub(), $lhs))->peephole();
            } else if ($current->kind === TokenKind::ExclamationMarkEquals) {
                $this->lexer->next();
                $lhs = (new NotNode((new CompNode(CompKind::Equal, $lhs, $this->parseAddSub()))->peephole()))->peephole();
            } else {
                break;
            }
        }
        return $lhs;
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
            return (new ConstantNode($current->value))->peephole();
        } else if ($current->kind === TokenKind::ParenLeft) {
            $this->lexer->next();
            $expr = $this->parseExpression();
            $this->consume(TokenKind::ParenRight);
            return $expr;
        } else if ($current->kind === TokenKind::Minus) {
            $this->lexer->next();
            $expr = $this->parseTerm();
            return (new NegNode($expr))->peephole();
        } else if ($current->kind === TokenKind::Identifier) {
            $this->lexer->next();
            assert($current instanceof IdentifierToken);
            return $this->symbolTable->lookup($current->name);
        } else {
            $this->unexpectedToken();
        }
    }

    private function consume(TokenKind $kind): Token
    {
        $current = $this->lexer->current();
        if ($current->kind !== $kind) {
            throw new \Exception('Unexpected token ' . $current->kind->name . ', expected ' . $kind->name);
        }
        $this->lexer->next();
        return $current;
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
