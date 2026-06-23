<?php

namespace Gordy\BrainFix\Node\Expression;

use Gordy\BrainFix;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\MemoryCellTyped;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Type;

class ScalarVariable implements Expression, Assignable
{
	use BrainFix\Node\HasToken;

	public function __construct(Token $name)
	{
		$this->token = $name;
	}

	public function compile(BrainFix\Environment $env) : void
	{
		// do nothing
	}

	public function name() : Token
	{
		return $this->token;
	}

	public function memoryCell(Environment $env) : MemoryCellTyped
	{
		return $env->memory()->get($this->name());
	}

	public function resultType(Environment $env) : Type\Type
	{
		return $this->memoryCell($env)->type();
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		$cell = $this->memoryCell($env);
		$env->processor()->copy($cell, $result);
	}

	public function hasVariable(string $name) : bool
	{
		return $this->name()->value() === $name;
	}

	public function assign(Environment $env, Expression $value, string $modifier) : void
	{
		$result = $value->resultType($env);

		if ($result instanceof Type\Computable)
		{
			$this->assignComputed($env, $result, $value, $modifier);
		}
		else if ($result instanceof Type\Scalar)
		{
			$this->assignVariable($env, $result, $value, $modifier);
		}
		else
		{
			throw new CompileError('scalar value expected', $value->token());
		}
	}

	protected function assignComputed(Environment $env, Type\Computable $result, Expression $value, string $modifier) : void
	{
		$this->checkAssignType($env, $value, $modifier);
		$isBool = $this->resultType($env) instanceof Type\Boolean;

		$memoryCell = $this->memoryCell($env);

		if (!$result->numericCompatible())
		{
			throw new CompileError('numeric type expected', $value->token());
		}

		$numericValue = $result->getNumeric();

		if ($modifier === self::ASSIGN_SET)
		{
			$env->processor()->unset($memoryCell);
			$env->processor()->addConstant($memoryCell, $isBool ? $numericValue !== 0 : $numericValue);
		}
		else if ($modifier === self::ASSIGN_ADD)
		{
			$env->processor()->addConstant($memoryCell, $numericValue);
		}
		else if ($modifier === self::ASSIGN_SUB)
		{
			$env->processor()->subConstant($memoryCell, $numericValue);
		}
		else
		{
			Expression\Calculation\Assignation::assignByConstant($env, $memoryCell, $numericValue, $modifier, $value->token());
		}
	}

	protected function assignVariable(Environment $env, Type\Scalar $result, Expression $value, string $modifier) : void
	{
		$this->checkAssignType($env, $value, $modifier);
		$memoryCell = $this->memoryCell($env);

		$castBool = $this->resultType($env) instanceof Type\Boolean
			&& !$value->resultType($env) instanceof Type\Boolean;
		// todo optimize a = b = 10

		$tempResult = $env->processor()->reserve($memoryCell);
		$value->compileCalculation($env, $tempResult);

		if ($modifier === self::ASSIGN_SET)
		{
			$env->processor()->unset($memoryCell);
			if ($castBool)
			{
				$env->processor()->moveBoolean($tempResult, $memoryCell);
			}
			else
			{
				$env->processor()->move($tempResult, $memoryCell);
			}
		}
		else if ($modifier === self::ASSIGN_ADD)
		{
			$env->processor()->add($tempResult, $memoryCell);
		}
		else if ($modifier === self::ASSIGN_SUB)
		{
			$env->processor()->sub($tempResult, $memoryCell);
		}
		else
		{
			Expression\Calculation\Assignation::assignByVariable($env, $memoryCell, $tempResult, $modifier, $value->token());
		}

		$env->processor()->release($tempResult);
	}

	protected function checkAssignType(Environment $env, Expression $value, string $modifier) : void
	{
		$isBool = $this->resultType($env) instanceof Type\Boolean;
		$isArithmetic = in_array($modifier, self::ASSIGN_ARITHMETIC);

		if ($isBool && $isArithmetic)
		{
			throw new CompileError("Why? It's bool variable. It's stupid. I won't do it.", $value->token());
		}
	}

	public function __toString() : string
	{
		return $this->token()->value();
	}
}