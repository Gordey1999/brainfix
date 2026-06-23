<?php

namespace Gordy\BrainFix\Node\Expression\Calculation;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\MemoryCell;

class Multiplication
{
	public static function assignByConstant(Environment $env, MemoryCell $cell, int $constant) : void
	{
		$temp = $env->processor()->reserve($cell);
		$env->processor()->move($cell, $temp);
		$env->processor()->multiplyByConstant($temp, $constant, $cell);
		$env->processor()->release($temp);
	}

	public static function assignByVariable(Environment $env, MemoryCell $cell, MemoryCell $value) : void
	{
		$temp = $env->processor()->reserve($cell, $value);
		$env->processor()->move($cell, $temp);
		$env->processor()->multiply($temp, $value, $cell);
		$env->processor()->release($temp);
	}
}