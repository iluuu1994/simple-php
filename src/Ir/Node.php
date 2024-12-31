<?php

namespace SimplePhp\Ir;

abstract class Node
{
    private static int $counter = 0;

    public int $id;

    /** @var list<Node|null> */
    public array $inputs;

    /** @var list<Node> */
    public array $outputs;

    /** @param list<Node|null> $inputs */
    public function __construct(array $inputs)
    {
        $this->id = self::$counter++;
        $this->inputs = $inputs;
        $this->outputs = [];

        foreach ($this->inputs as $input) {
            if ($input !== null) {
                $input->outputs[] = $this;
            }
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

        foreach ($this->inputs as $input) {
            if ($input !== null) {
                $input->removeOutput($this);
            }
        }
    }

    public function removeOutput(Node $node): void
    {
        $this->outputs = array_values(array_filter($this->outputs, fn ($output) => $output !== $node));
        if (!$this->isUsed()) {
            $this->kill();
        }
    }

    public function isUsed(): bool
    {
        return count($this->outputs) !== 0;
    }

    public static function resetIds(): void
    {
        self::$counter = 0;
    }

    public abstract function __toString(): string;
}
