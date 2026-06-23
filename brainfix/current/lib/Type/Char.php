<?php

namespace Gordy\BrainFix\Type;

class Char implements Scalar
{
	public function __toString() : string
	{
		return 'char';
	}
}