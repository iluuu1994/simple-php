<?php

namespace SimplePhp\Ir;

/* FIXME: This should potentially be a node itself to avoid killing referenced nodes prematurely. */
class SymbolTable
{
    /** @var array<int, array<string, DataNode>> */
    private array $scopes = [];

    public function pushScope(): void
    {
        $this->scopes[] = [];
    }

    public function popScope(): void
    {
        assert(!empty($this->scopes));
        array_pop($this->scopes);
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
    }
}
