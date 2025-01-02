<?php

namespace SimplePhp\Ir;

use SimplePhp\UnexpectedError;

class SymbolTable extends Node
{
    /** @var array<int, array<string, DataNode>> */
    private array $scopes = [];

    public function __construct()
    {
        parent::__construct([]);

        /* FIXME: Kind of ugly, but we don't care about the ID for the symbol table node. */
        $this->id = -1;
        self::$counter--;
    }

    public function pushScope(): void
    {
        $this->scopes[] = [];
    }

    public function popScope(): void
    {
        assert(!empty($this->scopes));
        $scope = array_pop($this->scopes);
        foreach ($scope as $node) {
            $this->removeInput($node);
        }
    }

    public function lookup(string $name): DataNode
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            $scope = $this->scopes[$i];
            if (isset($scope[$name])) {
                return $scope[$name];
            }
        }

        throw new \Exception("Undeclared identifier $name");
    }

    public function declare(string $name, DataNode $node): void
    {
        assert(!empty($this->scopes));
        $i = count($this->scopes) - 1;
        if (isset($this->scopes[$i][$name])) {
            throw new \Exception("Redeclaration of $name");
        }
        $this->scopes[$i][$name] = $node;
        $this->addInput($node);
    }

    public function update(string $name, DataNode $node): void
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            $scope = $this->scopes[$i];
            if (isset($scope[$name])) {
                $old = $this->scopes[$i][$name];
                $this->removeInput($old);
                $this->scopes[$i][$name] = $node;
                $this->addInput($node);
                return;
            }
        }

        throw new \Exception("Undeclared identifier $name");
    }

    private function removeInput(Node $node): void
    {
        foreach ($this->inputs as $i => $input) {
            if ($input === $node) {
                array_splice($this->inputs, $i, 1);
                $input->removeOutput($this);
                return;
            }
        }

        throw new UnexpectedError('Input was not present');
    }

    public function __toString(): string
    {
        throw new UnexpectedError('Should be removed from the graph before printing');
    }
}
