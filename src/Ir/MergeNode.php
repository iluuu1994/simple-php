<?php

namespace SimplePhp\Ir;

use SimplePhp\Inference\ControlType;

/**
 * @property list<ControlNode> $inputs
 */
class MergeNode extends ControlNode
{
    public function infer(): ControlType
    {
        foreach ($this->inputs as $input) {
            if ($input->infer() === ControlType::alive()) {
                return ControlType::alive();
            }
        }
        return ControlType::dead();
    }

    public function idealize(): ?self
    {
        return null;
    }

    // private function findDeadInput(): ?ControlNode
    // {
    //     foreach ($this->inputs as $input) {
    //         $type = $input->infer();
    //         if ($type === ControlType::dead()) {
    //             return $input;
    //         }
    //     }
    //
    //     return null;
    // }

    public function __toString(): string
    {
        return 'Merge';
    }
}
