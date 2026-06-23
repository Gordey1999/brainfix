<?php

namespace Gordy\BrainFix\Node\Expression;

use Gordy\BrainFix;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Node\HasToken;
use Gordy\BrainFix\Type;

class ArrayScope implements Expression
{
	use HasToken;

	protected Expression $expression;

	public function __construct(Expression $expr, Token $token)
	{
		$this->expression = $expr;
		$this->token = $token;
	}

	public function resultType(Environment $env) : Type\Type
	{
		if ($this->expression instanceof Expression\Operator\Comma)
		{
			$list = $this->expression->list();
		}
		else
		{
			$list = [ $this->expression ];
		}

		$result = [];
		foreach ($list as $item)
		{
			$itemResult = $item->resultType($env);
			if (!$itemResult instanceof Type\Computable)
			{
				throw new CompileError('only constant values allowed', $item->token());
			}
			if ($itemResult->arrayCompatible())
			{
				$result[] = $itemResult->getArray();
			}
			else if ($itemResult->numericCompatible())
			{
				$result[] = $itemResult->getNumeric();
			}
			else
			{
				throw new \Exception('not compatible');
			}
		}

		return new Type\Computable($result);
	}

	public function compile(BrainFix\Environment $env) : void
	{
		throw new CompileError('array scope not allowed here', $this->token);
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		throw new \Exception('not implemented');
	}

	public function hasVariable(string $name) : bool
	{
		return false;
	}

	public function __toString() : string
	{
		return sprintf('[%s]', $this->expression);
	}
}