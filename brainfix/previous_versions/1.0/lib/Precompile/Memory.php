<?php

namespace Gordy\Brainfuck\BigBrain\Precompile;

use Gordy\Brainfuck\BigBrain\MemoryCellTyped;
use Gordy\Brainfuck\BigBrain\Parser\Token;
use Gordy\Brainfuck\BigBrain\Type;
use Gordy\Brainfuck\BigBrain\Memory as BaseMemory;

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