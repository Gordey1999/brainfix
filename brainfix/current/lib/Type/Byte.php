<?php

namespace Gordy\BrainFix\Type;

class Byte implements Scalar
{
	public function __toString() : string
	{
		return 'byte';
	}
}