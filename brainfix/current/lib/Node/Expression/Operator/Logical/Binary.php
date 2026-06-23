<?php

namespace Gordy\BrainFix\Node\Expression\Operator\Logical;

use Gordy\BrainFix\Type;
use Gordy\BrainFix\Node\Expression;

abstract class Binary extends Expression\Operator\Binary
{
	protected abstract function computeValue(int $left, int $right) : bool;

	protected function computeResultType() : Type\BaseType
	{
		return new Type\Boolean();
	}
}