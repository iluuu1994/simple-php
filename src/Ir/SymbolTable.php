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
    }

    public function pushScope(): void
    {
        assert(!$this->killed);
        $this->scopes[] = [];
    }

    public function popScope(): void
    {
        /* Keep killed symbol tables linked to avoid null checks, but they must be
         * replaced before adding more elements. */
        if ($this->killed) {
            assert(empty($this->scopes));
            return;
        }
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

    public function kill(): void
    {
        /* FIXME: Kind of ugly. Allow multiple kills. */
        $this->killed = false;
        parent::kill();
        $this->inputs = [];
        $this->scopes = [];
    }

    public function __toString(): string
    {
        throw new UnexpectedError('Should be removed from the graph before printing');
    }

    public function __clone()
    {
        foreach ($this->inputs as $input) {
            $input->outputs[] = $this;
        }
    }

    public static function merged(self $lhs, self $rhs, MergeNode $mergeNode): self
    {
        assert(count($lhs->scopes) === count($rhs->scopes));

        $new = new self();

        foreach ($lhs->scopes as $i => $lhsScope) {
            $rhsScope = $rhs->scopes[$i];
            assert(count($lhsScope) === count($rhsScope));

            $new->pushScope();

            foreach ($lhsScope as $name => $lhsNode) {
                $rhsNode = $rhsScope[$name];
                if ($lhsNode === $rhsNode) {
                    $new->declare($name, $lhsNode);
                } else {
                    $new->declare($name, new PhiNode($mergeNode, [$lhsNode, $rhsNode]));
                }
            }
        }

        return $new;
    }
}
