<?php

namespace Gordy\BrainFix\Type;

class Boolean implements Scalar
{
	public function __toString() : string
	{
		return 'bool';
	}
}