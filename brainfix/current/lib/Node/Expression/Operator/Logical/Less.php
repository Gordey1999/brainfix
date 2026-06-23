<?php

namespace Gordy\BrainFix\Node\Expression\Operator\Logical;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\MemoryCell;

class Less extends More
{
	protected function computeValue(int $left, int $right) : bool
	{
		return $left < $right;
	}

	protected function calculate(Environment $env, MemoryCell $a, MemoryCell $b, MemoryCell $result) : void
	{
		$proc = $env->processor();
		$proc->subUntilZero($b, $a);
		$proc->moveBoolean($b, $result);
	}

	public function __toString() : string
	{
		return sprintf('(%s < %s)', $this->left, $this->right);
	}
}