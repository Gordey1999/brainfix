<?php

namespace Gordy\BrainFix;

class MemoryPointer
{
	private int $address = 0;
	private ?\Closure $returnToValidState = null;

	public function set(int $address) : void
	{
		$this->ensureValidState();
		$this->address = $address;
	}

	public function get() : int
	{
		$this->ensureValidState();
		return $this->address;
	}

	public function setLostState(callable $returnCallback) : void
	{
		$this->returnToValidState = $returnCallback;
	}

	public function ensureValidState() : void
	{
		if ($this->returnToValidState !== null)
		{
			$callback = $this->returnToValidState;
			$this->returnToValidState = null;
			$this->address = $callback();
		}
	}
}