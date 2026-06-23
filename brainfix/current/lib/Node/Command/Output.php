<?php

namespace Gordy\BrainFix\Node\Command;

use Gordy\BrainFix;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\Node\Expression\Operator\ArrayAccess;
use Gordy\BrainFix\Utils;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Type;

class Output implements Node\Command
{
	use Node\HasToken;

	/** @var Expression[] */
	private array $parts;

	public function __construct(Expression $expr, Token $token)
	{
		$this->parts = $this->getParts($expr);
		$this->token = $token;
	}

	/** @return Expression[] */
	protected function getParts(Expression $expr) : array
	{
		if ($expr instanceof Expression\Operator\Comma)
		{
			return $expr->list();
		}
		else
		{
			return [ $expr ];
		}
	}

	public function compile(BrainFix\Environment $env) : void
	{
		foreach ($this->parts as $part)
		{
			$env->stream()->blockComment("out $part");

			$resultType = $part->resultType($env);

			if ($resultType instanceof Type\Computable)
			{
				$this->printComputable($env, $resultType);
			}
			else if ($resultType instanceof Type\Pointer && $resultType->valueType() instanceof Type\Char)
			{
				$this->printString($env, $part, $resultType);
			}
			else if ($part instanceof Expression\ScalarVariable && $resultType instanceof Type\Char)
			{
				$env->processor()->print($part->memoryCell($env));
			}
			else if ($part instanceof ArrayAccess && $resultType instanceof Type\Char)
			{
				$this->printArrayIndex($env, $part);
			}
			else if ($resultType instanceof Type\Scalar)
			{
				$this->printExpression($env, $part, $resultType);
			}
			else
			{
				throw new CompileError("command out: type '$resultType' not supported", $part->token());
			}
		}
	}

	protected function printComputable(Environment $env, Type\Computable $result) : void
	{
		$temp = $env->processor()->reserve();
		$bytes = Utils\CharHelper::stringToBytes($result->getString());

		$last = 0;
		foreach ($bytes as $byte)
		{
			$add = $byte - $last;
			$env->processor()->addConstant($temp, $add);
			$env->processor()->print($temp);
			$last = $byte;
		}

		$env->processor()->unset($temp);
		$env->processor()->release($temp);
	}

	protected function printString(Environment $env, Expression $part, Type\Pointer $resultType) : void
	{
		if ($part instanceof Expression\ArrayVariable)
		{
			$env->arraysProcessor()->computeIndex($part->memoryCell($env)->startIndex(), true);
			$env->arraysProcessor()->printString($resultType->size());
		}
		else if ($part instanceof ArrayAccess)
		{
			$part->calculateIndex($env);
			$env->arraysProcessor()->printString($resultType->size());
		}
		else
		{
			throw new CompileError("command out: type '$resultType' not supported", $part->token());
		}
	}

	protected function printArrayIndex(Environment $env, ArrayAccess $expr) : void
	{
		$expr->calculateIndex($env);
		$env->arraysProcessor()->print();
	}

	protected function printExpression(Environment $env, Expression $expr, Type\Scalar $resultType) : void
	{
		$result = $env->processor()->reserve();

		$expr->compileCalculation($env, $result);

		if ($resultType instanceof Type\Char)
		{
			$env->processor()->print($result);
			$env->processor()->unset($result);
		}
		else if ($resultType instanceof Type\Boolean)
		{
			$env->processor()->addConstant($result, 48);
			$env->processor()->print($result);
			$env->processor()->unset($result);
		}
		else if ($resultType instanceof Type\Byte)
		{
			$env->processor()->printNumber($result);
		}
		else
		{
			throw new CompileError("unsupported output type '$resultType'", $expr->token());
		}

		$env->processor()->release($result);
	}

	public function __toString() : string
	{
		return 'out ' . implode(', ', $this->parts);
	}
}