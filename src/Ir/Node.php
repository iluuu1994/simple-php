<?php

namespace SimplePhp\Ir;

use SimplePhp\UnexpectedError;

abstract class Node
{
    protected static int $counter = 0;

    public int $id;

    /** @var list<Node> */
    public array $inputs;

    /** @var list<Node> */
    public array $outputs;

    protected bool $killed = false;

    /** @param list<Node> $inputs */
    public function __construct(array $inputs)
    {
        $this->id = self::$counter++;
        $this->inputs = $inputs;
        $this->outputs = [];

        foreach ($this->inputs as $input) {
            $input->outputs[] = $this;
        }
    }

    public function addInput(Node $input): void
    {
        $this->inputs[] = $input;
        $input->outputs[] = $this;
    }

    public function getInput(int $i): ?Node
    {
        if ($i >= count($this->inputs)) {
            throw new \Exception("Input $i is out of bounds");
        }
        return $this->inputs[$i];
    }

    public function kill(): void
    {
        assert(!$this->isUsed());
        assert(!$this->killed);

        $this->killed = true;

        foreach ($this->inputs as $input) {
            $input->removeOutput($this);
        }
    }

    public function removeOutput(Node $node): void
    {
        foreach ($this->outputs as $i => $output) {
            if ($output === $node) {
                array_splice($this->outputs, $i, 1);
                if (!$this->isUsed()) {
                    $this->kill();
                }
                return;
            }
        }

        throw new UnexpectedError('Output ' . $node->id . ' was not present in ' . $this->id);
    }

    public function isUsed(): bool
    {
        return count($this->outputs) !== 0;
    }

    /** Keeps replacement alive while killing self. */
    public function dce(Node $replacement): void
    {
        if ($this !== $replacement && !$this->isUsed()) {
            $replacement->keep();
            $this->kill();
            $replacement->unkeep();
        }
    }

    public function keep(): void
    {
        $this->outputs[] = DummyNode::get();
    }

    public function unkeep(): void
    {
        foreach ($this->outputs as $i => $output) {
            if ($output instanceof DummyNode) {
                array_splice($this->outputs, $i, 1);
                return;
            }
        }

        throw new UnexpectedError('Node was not kept');
    }

    public static function resetIds(): void
    {
        self::$counter = 0;
    }

    abstract public function __toString(): string;
}
