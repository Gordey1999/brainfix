<?php

namespace Gordy\Brainfuck\BigBrain\Data;

use Gordy\Brainfuck\BigBrain\MemoryCellArray;

class IndexData
{
	private MemoryCellArray $arrayCell;

	private int $computedOffset = 0;
	private bool $useDynamicOffset = false;

	public function __construct(MemoryCellArray $arrayCell)
	{
		$this->arrayCell = $arrayCell;
	}

	public function addComputedOffset(int $offset) : void
	{
		$this->computedOffset += $offset;
	}

	public function setUseDynamicOffset(bool $value = true) : bool
	{
		return $this->useDynamicOffset = $value;
	}

	public function startOffset() : int
	{
		return $this->arrayCell->startIndex();
	}

	public function computedOffset() : int
	{
		return $this->computedOffset;
	}

	public function useDynamicOffset() : bool
	{
		return $this->useDynamicOffset;
	}

	public function maxDynamicOffset() : int
	{
		if (!$this->useDynamicOffset())
		{
			return 0;
		}
		return $this->arrayCell->type()->plainSize();
	}
}