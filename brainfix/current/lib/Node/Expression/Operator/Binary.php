<?php

namespace Gordy\BrainFix\Node\Expression\Operator;

use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Node;
use Gordy\BrainFix\Type;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;

abstract class Binary implements Expression
{
	use Node\HasToken;

	protected Expression $left;
	protected Expression $right;

	public function __construct(Expression $left, Expression $right, Token $token)
	{
		$this->left = $left;
		$this->right = $right;
		$this->token = $token;
	}

	protected abstract function computeValue(int $left, int $right) : mixed;

	protected abstract function compileForVariables(Environment $env, MemoryCell $result) : void;

	protected abstract function compileWithLeftConstant(Environment $env, int $constant, MemoryCell $result) : void;

	protected abstract function compileWithRightConstant(Environment $env, int $constant, MemoryCell $result) : void;

	protected abstract function computeResultType() : Type\BaseType;

	public function compile(Environment $env) : void
	{
		$this->left->compile($env);
		$this->right->compile($env);
	}

	public function resultType(Environment $env) : Type\Type
	{
		$leftType = $this->left->resultType($env);
		$rightType = $this->right->resultType($env);

		if ($leftType instanceof Type\Computable && $rightType instanceof Type\Computable)
		{
			$this->checkComputedType($leftType);
			$this->checkComputedType($rightType);

			$result = $this->computeValue($leftType->getNumeric(), $rightType->getNumeric());
			return new Type\Computable($result);
		}

		return $this->computeResultType();
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		$resultType = $this->resultType($env);
		if ($resultType instanceof Type\Computable)
		{
			$env->processor()->addConstant($result, $resultType->value());
			return;
		}

		$leftType = $this->left->resultType($env);
		$rightType = $this->right->resultType($env);

		if ($leftType instanceof Type\Computable)
		{
			$this->checkComputedType($leftType);
			$this->checkScalarType($rightType);
			$this->compileWithLeftConstant($env, $leftType->getNumeric(), $result);
		}
		else if ($rightType instanceof Type\Computable)
		{
			$this->checkComputedType($rightType);
			$this->checkScalarType($leftType);
			$this->compileWithRightConstant($env, $rightType->getNumeric(), $result);
		}
		else
		{
			$this->compileForVariables($env, $result);
		}
	}

	public function hasVariable(string $name) : bool
	{
		return $this->left->hasVariable($name) || $this->right->hasVariable($name);
	}

	protected function checkComputedType(Type\Computable $value) : void
	{
		if (!$value->numericCompatible())
		{
			throw new CompileError(sprintf('unsupported operand type "%s" for operator "%s"',
				$value->type(),
				$this->token->value(),
			), $this->token);
		}
	}

	protected function checkScalarType(Type\Type $value) : void
	{
		if (!$value instanceof Type\Scalar)
		{
			throw new CompileError(sprintf('unsupported operand type "%s" for operator "%s"',
				$value,
				$this->token->value(),
			), $this->token);
		}
	}
}