<?php

namespace Gordy\BrainFix\Node\Expression\Operator\Assignment;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Node\Expression;

class Addition extends Skeleton
{
	protected function assign(Environment $env) : void
	{
		$this->to->assign($env, $this->value, Expression\Assignable::ASSIGN_ADD);
	}

	public function __toString() : string
	{
		return sprintf('%s += %s', $this->to, $this->value);
	}
}