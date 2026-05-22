<?php

namespace Gordy\Brainfuck\BigBrain\Precompile;

use Gordy\Brainfuck\BigBrain\MemoryCell;
use Gordy\Brainfuck\BigBrain\TrickyProcessor as BaseTrickyProcessor;

class TrickyProcessor extends BaseTrickyProcessor
{
	protected bool $used = false;

	protected function _divide(MemoryCell $a, MemoryCell $result, MemoryCell $remainder) : void
	{
		$this->used = true;
		parent::_divide($a, $result, $remainder);
	}

	protected function _isMore(MemoryCell $b, MemoryCell $result) : void
	{
		$this->used = true;
		parent::_isMore($b, $result);
	}

	public function computedRegistrySize() : int
	{
		return $this->used ? 5 : 0;
	}
}