<?php

namespace Gordy\BrainFix\Node\Expression\Calculation;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression\Assignable;

class Assignation
{
	public static function assignByConstant(
		Environment $env,
		MemoryCell $cell,
		int $constant,
		string $modifier,
		Token $token
	) : void
	{
		match ($modifier) {
			Assignable::ASSIGN_MULTIPLY => Multiplication::assignByConstant($env, $cell, $constant),
			Assignable::ASSIGN_DIVIDE => Division::assignByConstant($env, $cell, $constant, $token),
			Assignable::ASSIGN_MODULO => Modulo::assignByConstant($env, $cell, $constant, $token),
			default => throw new CompileError('undefined modifier', $token),
		};
	}

	public static function assignByVariable(
		Environment $env,
		MemoryCell $cell,
		MemoryCell $value,
		string $modifier,
		Token $token
	) : void
	{
		match ($modifier) {
			Assignable::ASSIGN_MULTIPLY => Multiplication::assignByVariable($env, $cell, $value),
			Assignable::ASSIGN_DIVIDE => Division::assignByVariable($env, $cell, $value, $token),
			Assignable::ASSIGN_MODULO => Modulo::assignByVariable($env, $cell, $value, $token),
			default => throw new CompileError('undefined modifier', $token),
		};
	}
}