<?php

namespace Gordy\Brainfuck\BigBrain\Node\Expression\Operator;

use Gordy\Brainfuck\BigBrain;
use Gordy\Brainfuck\BigBrain\Data\IndexData;
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

	public function calculateIndex(Environment $env, MemoryCell $result) : IndexData
	{
		$sizes = $this->startCell($env)->type()->sizes();
		$multipliers = Utils\ArraysHelper::indexMultipliers($sizes);
		$indexes = $this->indexes($env);

		$data = new IndexData($this->startCell($env));
		foreach ($indexes as $key => $index)
		{
			$indexResult = $index->resultType($env);
			if ($indexResult instanceof Type\Computable)
			{
				if (!$indexResult->numericCompatible())
				{
					throw new CompileError('numeric index expected', $this->token());
				}
				$data->addComputedOffset($indexResult->getNumeric() * $multipliers[$key]);
			}
			else
			{
				$data->setUseDynamicOffset();
				if ($multipliers[$key] === 1)
				{
					$index->compileCalculation($env, $result);
				}
				else
				{
					$temp = $env->processor()->reserve($result);
					$index->compileCalculation($env, $temp);
					$env->processor()->multiplyByConstant($temp, $multipliers[$key], $result);
					$env->processor()->release($temp);
				}
			}
		}

		return $data;
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		$type = $this->resultType($env);
		if ($type instanceof Type\Scalar)
		{
			$startCell = $env->arraysProcessor()->startCell();
			$indexData = $this->calculateIndex($env, $startCell);
			$carry = $env->arraysProcessor()->get($indexData);
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
			$indexCell = $env->arraysProcessor()->startCell();
			$indexData = $this->calculateIndex($env, $indexCell);
			$plainArray = $this->variable()->prepareArrayValues($env, $selfType, $value);
			$env->arraysProcessor()->fill($indexData, $plainArray);
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

		$indexData = $this->calculateAssignIndex($env, $modifier);

		if (!$result->numericCompatible())
		{
			throw new CompileError('numeric type expected', $value->token());
		}

		$numericValue = $result->getNumeric();

		if ($modifier === self::ASSIGN_SET)
		{
			$env->arraysProcessor()->setConstant($indexData, $isBool ? $numericValue !== 0 : $numericValue);
		}
		else if ($modifier === self::ASSIGN_ADD)
		{
			$env->arraysProcessor()->addConstant($indexData, $numericValue);
		}
		else if ($modifier === self::ASSIGN_SUB)
		{
			$env->arraysProcessor()->addConstant($indexData, -$numericValue);
		}
		else
		{
			$dummyCell = $env->arraysProcessor()->dummyCell();
			$carryCell = $env->arraysProcessor()->carryCell();
			$valueCell = $env->arraysProcessor()->get($indexData);

			if ($modifier === self::ASSIGN_MULTIPLY)
			{
				$env->processor()->multiplyByConstant($valueCell, $numericValue, $carryCell);
			}
			else if ($modifier === self::ASSIGN_DIVIDE)
			{
				Expression\Calculation\Division::divideByConstant($env, $valueCell, $numericValue, $carryCell);
			}
			else if ($modifier === self::ASSIGN_MODULO)
			{
				Expression\Calculation\Modulo::divideByConstant($env, $valueCell, $numericValue, $carryCell);
			}
			else
			{
				throw new CompileError('undefined modifier', $this->token);
			}
			$env->processor()->move($dummyCell, $carryCell);
			$env->arraysProcessor()->set($indexData);
		}
	}

	protected function assignVariable(Environment $env, Expression $value, string $modifier) : void
	{
		$this->checkAssignType($env, $value, $modifier);
		$castBool = $this->resultType($env) instanceof Type\Boolean
			&& !$value->resultType($env) instanceof Type\Boolean;

		$indexData = $this->calculateAssignIndex($env, $modifier);

		$startCell = $env->arraysProcessor()->startCell();
		$carryCell = $env->arraysProcessor()->carryCell();

		if ($modifier === self::ASSIGN_SET)
		{
			if ($castBool)
			{
				$tempResult = $env->processor()->reserve($startCell);
				$value->compileCalculation($env, $tempResult);
				$env->processor()->moveBoolean($tempResult, $carryCell);
				$env->processor()->release($tempResult);
				$env->arraysProcessor()->set($indexData);
			}
			else
			{
				$value->compileCalculation($env, $carryCell);
				$env->arraysProcessor()->set($indexData);
			}
		}
		else if ($modifier === self::ASSIGN_ADD)
		{
			$value->compileCalculation($env, $carryCell);
			$env->arraysProcessor()->add($indexData);
		}
		else if ($modifier === self::ASSIGN_SUB)
		{
			$value->compileCalculation($env, $carryCell);
			$env->arraysProcessor()->sub($indexData);
		}
		else
		{
			$tempResult = $env->processor()->reserve($startCell);
			$value->compileCalculation($env, $tempResult);
			$dummyCell = $env->arraysProcessor()->dummyCell();
			$valueCell = $env->arraysProcessor()->get($indexData);

			if ($modifier === self::ASSIGN_MULTIPLY)
			{
				$env->processor()->multiply($valueCell, $tempResult, $carryCell);
			}
			else if ($modifier === self::ASSIGN_DIVIDE)
			{
				Expression\Calculation\Division::divide($env, $valueCell, $tempResult, $carryCell);
			}
			else if ($modifier === self::ASSIGN_MODULO)
			{
				Expression\Calculation\Modulo::divide($env, $valueCell, $tempResult, $carryCell);
			}
			else
			{
				throw new CompileError('undefined modifier', $this->token);
			}
			$env->processor()->move($dummyCell, $startCell);
			$env->arraysProcessor()->set($indexData);
			$env->processor()->release($tempResult);
		}
	}

	protected function calculateAssignIndex(Environment $env, string $modifier) : IndexData
	{
		$startCell = $env->arraysProcessor()->startCell();
		$dummyCell = $env->arraysProcessor()->dummyCell();

		if ($this->isSimpleModifier($modifier))
		{
			$indexData = $this->calculateIndex($env, $startCell);
		}
		else
		{
			$temp = $env->processor()->reserve($startCell);
			$indexData = $this->calculateIndex($env, $temp);
			$env->processor()->move($temp, $startCell, $dummyCell);
			$env->processor()->release($temp);
		}

		return $indexData;
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