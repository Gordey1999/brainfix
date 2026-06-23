<?php

namespace Gordy\BrainFix\Node\Expression;

use Gordy\BrainFix\Utils;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Type;
use Gordy\BrainFix\Node;
use Gordy\BrainFix\Exception\CompileError;

class Literal implements Expression
{
	use Node\HasToken;

	public function __construct(Token $token)
	{
		$this->token = $token;
	}

	public function compile(Environment $env) : void
	{
		// do nothing
	}

	public function resultType(Environment $env) : Type\Computable
	{
		$value = $this->token->value();
		$parsed = match(true) {
			$value[0] === '"' || $value[0] === "'" => Utils\CharHelper::convertSpecialChars(
				substr($value, 1, -1)
			),
			$value === 'true' || $value === 'false' => $value === 'true',
			$value === 'eol' => "\n",
			ctype_digit($value) => (int)$value,
			default => throw new CompileError('not supported type', $value),
		};

		return new Type\Computable($parsed);
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		$env->processor()->addConstant($result, $this->resultType($env)->getNumeric());
	}

	public function hasVariable(string $name) : bool
	{
		return false;
	}

	public function __toString() : string
	{
		return $this->token()->value();
	}
}