<?php

namespace Gordy\BrainFix\Precompile;

use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\ArraysMemory as BaseArraysMemory;
use Gordy\BrainFix\Type;
use Gordy\BrainFix\MemoryCellArray;

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