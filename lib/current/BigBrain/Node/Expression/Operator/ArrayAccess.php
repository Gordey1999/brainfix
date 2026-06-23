<?php

namespace Gordy\Brainfuck\BigBrain\Node\Expression\Operator;

use Gordy\Brainfuck\BigBrain;
use Gordy\Brainfuck\BigBrain\Environment;
use Gordy\Brainfuck\BigBrain\Exception\CompileError;
use Gordy\Brainfuck\BigBrain\Exception\SyntaxError;
use Gordy\Brainfuck\BigBrain\MemoryCell;
use Gordy\Brainfuck\BigBrain\MemoryCellArray;
use Gordy\Brainfuck\BigBrain\Parser\Token;
use Gordy\Brainfuck\BigBrain\Node\Expression;
use Gordy\Brainfuck\BigBrain\Node\HasToken;
use Gordy\Brainfuck\BigBrain\Type;
use Gordy\Brainfuck\BigBrain\Utils;

class ArrayAccess implements Expression, Expression\Assignable
{
	use HasToken;

	protected Expression $to;
	protected Expression $index;

	public function __construct(Expression $to, Expression $index, Token $token)
	{
		$this->to = $to;
		$this->index = $index;
		$this->token = $token;
	}

	public function variable() : Expression\ArrayVariable
	{
		if ($this->to instanceof Expression\ArrayVariable)
		{
			return $this->to;
		}
		if ($this->to instanceof self)
		{
			return $this->to->variable();
		}

		throw new SyntaxError('array name expected', $this->to->token());
	}

	public function dimensions(Environment $env) : array
	{
		$resultType = $this->index->resultType($env);

		if (!$resultType instanceof Type\Computable)
		{
			throw new CompileError('array size must be constant', $this->index->token());
		}
		if (!$resultType->numericNullableCompatible())
		{
			throw new CompileError('numeric expected', $this->index->token());
		}

		if ($this->to instanceof self)
		{
			return array_merge(
				$this->to->dimensions($env),
				[ $resultType->getNumericNullable() ]
			);
		}
		return [$resultType->getNumericNullable()];
	}

	public function resultType(Environment $env) : Type\BaseType
	{
		$result = $this->to->resultType($env);

		if ($result instanceof Type\Pointer)
		{
			return $result->valueType();
		}
		else
		{
			throw new CompileError('array operand expected, scalar passed', $this->to->token());
		}
	}

	public function compile(BigBrain\Environment $env) : void
	{
		$this->to->compile($env);
	}

	protected function startCell($env) : MemoryCellArray
	{
		return $this->variable()->memoryCell($env);
	}

	/** @return Expression[] */
	protected function indexes(Environment $env) : array
	{
		$result = [];
		if ($this->to instanceof self)
		{
			$result = $this->to->indexes($env);
		}
		$result[] = $this->index;

		$sizes = $this->startCell($env)->type()->sizes();
		if (count($result) > count($sizes))
		{
			throw new CompileError(
				sprintf('wrong index count. Array has only %s dimensions', count($sizes)),
				$this->token()
			);
		}

		return $result;
	}

	public function calculateIndex(Environment $env) : void
	{
		$sizes = $this->startCell($env)->type()->sizes();
		$multipliers = Utils\ArraysHelper::indexMultipliers($sizes);
		$indexes = $this->indexes($env);

		$firstIndex = true;
		$computedIndex = $this->startCell($env)->startIndex();
		foreach (array_reverse($indexes, true) as $key => $index)
		{
			$indexResult = $index->resultType($env);
			if ($indexResult instanceof Type\Computable)
			{
				if (!$indexResult->numericCompatible())
				{
					throw new CompileError('numeric index expected', $this->token());
				}
				$computedIndex += $indexResult->getNumeric() * $multipliers[$key];
			}
			else
			{
				if ($firstIndex)
				{
					// todo compileCalculation нужно делать раньше. Иначе не будет работать arr[arr2[1]]
					$index->compileCalculation($env, $env->arraysProcessor()->startCell());
					$env->arraysProcessor()->calculateIndex($multipliers[$key]);
					$firstIndex = false;
				}
				else
				{
					$index->compileCalculation($env, $env->arraysProcessor()->carryCell());
					$env->arraysProcessor()->calculateAddedIndex($multipliers[$key]);
				}
			}
		}

		if ($computedIndex > 0)
		{
			$env->arraysProcessor()->computeIndex($computedIndex, $firstIndex);
		}
	}

	protected function indexCalculationNeedsCarry(Environment $env) : bool
	{
		$indexes = $this->indexes($env);

		foreach ($indexes as $index)
		{
			if (!$index->resultType($env) instanceof Type\Computable)
			{
				return true;
			}
		}

		return false;
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		$type = $this->resultType($env);
		if ($type instanceof Type\Scalar)
		{
			$this->calculateIndex($env);
			$carry = $env->arraysProcessor()->getValue();
			$env->arraysProcessor()->clearIndex();
			$env->processor()->move($carry, $result);
		}
		else
		{
			throw new CompileError('not expected', $this->variable()->token());
		}
	}

	public function assign(Environment $env, Expression $value, string $modifier) : void
	{
		$selfType = $this->resultType($env);

		if ($selfType instanceof Type\Pointer)
		{
			if ($modifier !== self::ASSIGN_SET)
			{
				throw new CompileError('only "=" operator supported to fill array', $value->token());
			}
			$this->calculateIndex($env);
			$plainArray = $this->variable()->prepareArrayValues($env, $selfType, $value);
			$env->arraysProcessor()->fill($plainArray);
		}
		else if ($selfType instanceof Type\Scalar)
		{
			$this->assignScalar($env, $value, $modifier);
		}
		else
		{
			throw new CompileError('not expected', $this->token);
		}
	}

	protected function assignScalar(Environment $env, Expression $value, string $modifier) : void
	{
		$result = $value->resultType($env);

		if ($result instanceof Type\Computable)
		{
			$this->assignComputed($env, $result, $value, $modifier);
		}
		else if ($result instanceof Type\Scalar)
		{
			$this->assignVariable($env, $value, $modifier);
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

		$this->calculateIndex($env);

		if (!$result->numericCompatible())
		{
			throw new CompileError('numeric type expected', $value->token());
		}

		$numericValue = $result->getNumeric();

		if ($modifier === self::ASSIGN_SET)
		{
			$env->arraysProcessor()->setConstant($isBool ? $numericValue !== 0 : $numericValue);
		}
		else if ($modifier === self::ASSIGN_ADD)
		{
			$env->arraysProcessor()->addConstant($numericValue);
		}
		else if ($modifier === self::ASSIGN_SUB)
		{
			$env->arraysProcessor()->addConstant(-$numericValue);
		}
		else
		{
			$carryCell = $env->arraysProcessor()->takeValue();
			$tmp = $env->processor()->reserve($carryCell);

			if ($modifier === self::ASSIGN_MULTIPLY)
			{
				$env->processor()->multiplyByConstant($carryCell, $numericValue, $tmp);
			}
			else if ($modifier === self::ASSIGN_DIVIDE)
			{
				Expression\Calculation\Division::divideByConstant($env, $carryCell, $numericValue, $tmp);
			}
			else if ($modifier === self::ASSIGN_MODULO)
			{
				Expression\Calculation\Modulo::divideByConstant($env, $carryCell, $numericValue, $tmp);
			}
			else
			{
				throw new CompileError('undefined modifier', $this->token);
			}
			$env->processor()->move($tmp, $carryCell);
			$env->arraysProcessor()->add();
			$env->processor()->release($tmp);
		}
	}

	protected function assignVariable(Environment $env, Expression $value, string $modifier) : void
	{
		$this->checkAssignType($env, $value, $modifier);
		$castBool = $this->resultType($env) instanceof Type\Boolean
			&& !$value->resultType($env) instanceof Type\Boolean;

		$carryCell = $env->arraysProcessor()->carryCell();
		$tempResult = $env->processor()->reserve($carryCell);

		$value->compileCalculation($env, $tempResult);
		$this->calculateIndex($env);

		if ($modifier === self::ASSIGN_SET)
		{
			$env->arraysProcessor()->unsetValue();
			if ($castBool)
			{
				$env->processor()->moveBoolean($tempResult, $carryCell);
			}
			else
			{
				$env->processor()->move($tempResult, $carryCell);
			}
			$env->arraysProcessor()->moveCarryToValue();
			$env->arraysProcessor()->clearIndex();
		}
		else if ($modifier === self::ASSIGN_ADD)
		{
			$env->processor()->move($tempResult, $carryCell);
			$env->arraysProcessor()->add();
		}
		else if ($modifier === self::ASSIGN_SUB)
		{
			$env->processor()->move($tempResult, $carryCell);
			$env->arraysProcessor()->sub();
		}
		else
		{
			$tempResult2 = $env->processor()->reserve($tempResult);

			$env->arraysProcessor()->takeValue();

			if ($modifier === self::ASSIGN_MULTIPLY)
			{
				$env->processor()->multiply($carryCell, $tempResult, $tempResult2);
			}
			else if ($modifier === self::ASSIGN_DIVIDE)
			{
				Expression\Calculation\Division::divide($env, $carryCell, $tempResult, $tempResult2);
			}
			else if ($modifier === self::ASSIGN_MODULO)
			{
				Expression\Calculation\Modulo::divide($env, $carryCell, $tempResult, $tempResult2);
			}
			else
			{
				throw new CompileError('undefined modifier', $this->token);
			}
			$env->processor()->move($tempResult2, $carryCell);
			$env->arraysProcessor()->add();

			$env->processor()->release($tempResult2);
		}
		$env->processor()->release($tempResult);
	}

	protected function isSimpleModifier(string $modifier) : bool
	{
		return in_array($modifier, [self::ASSIGN_SET, self::ASSIGN_ADD, self::ASSIGN_SUB], true);
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

	public function hasVariable(string $name) : bool
	{
		return $this->index->hasVariable($name) || $this->to->hasVariable($name);
	}

	public function __toString() : string
	{
		return sprintf('%s[%s]', $this->to, $this->index);
	}
}