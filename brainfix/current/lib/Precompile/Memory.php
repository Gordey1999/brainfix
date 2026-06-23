<?php

namespace Gordy\BrainFix\Precompile;

use Gordy\BrainFix\MemoryCellTyped;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Type;
use Gordy\BrainFix\Memory as BaseMemory;

class Memory extends BaseMemory
{
	protected int $maxMemorySize = 0;

	public function allocate(Type\BaseType $type, Token $name) : MemoryCellTyped
	{
		$cell = parent::allocate($type, $name);
		$this->maxMemorySize = max($this->maxMemorySize, $this->count());

		return $cell;
	}

	public function computedMemorySize() : int
	{
		return $this->maxMemorySize;
	}
}