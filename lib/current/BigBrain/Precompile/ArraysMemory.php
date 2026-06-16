<?php

namespace Gordy\Brainfuck\BigBrain\Precompile;

use Gordy\Brainfuck\BigBrain\Parser\Token;
use Gordy\Brainfuck\BigBrain\ArraysMemory as BaseArraysMemory;
use Gordy\Brainfuck\BigBrain\Type;
use Gordy\Brainfuck\BigBrain\MemoryCellArray;

class ArraysMemory extends BaseArraysMemory
{
	protected int $maxMemorySize = 0;

	public function allocate(Type\BaseType $type, Token $name, array $sizes) : MemoryCellArray
	{
		$cell = parent::allocate($type, $name, $sizes);
		$this->maxMemorySize = max($this->maxMemorySize, $this->allocatedSize());

		return $cell;
	}

	public function computedMemorySize() : int
	{
		return $this->maxMemorySize;
	}
}