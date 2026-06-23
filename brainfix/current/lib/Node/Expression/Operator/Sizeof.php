<?php

namespace Gordy\BrainFix\Node\Expression\Operator;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Node;
use Gordy\BrainFix\Type;

class Sizeof implements Expression
{
	use Node\HasToken;

	protected Expression $value;

	public function __construct(Expression $value, Token $token)
	{
		$this->value = $value;
		$this->token = $token;
	}

	public function compile(Environment $env) : void
	{
		$this->value->compile($env);
	}

	public function resultType(Environment $env) : Type\Type
	{
		$valueType = $this->value->resultType($env);
		if ($valueType instanceof Type\Pointer)
		{
			return new Type\Computable($valueType->size());
		}
		else
		{
			throw new CompileError('array expected', $this->value->token());
		}
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		throw new CompileError('not expected', $this->value->token());
	}

	public function hasVariable(string $name) : bool
	{
		return $this->value->hasVariable($name);
	}

	public function __toString() : string
	{
		return sprintf('sizeof %s', $this->value);
	}
}