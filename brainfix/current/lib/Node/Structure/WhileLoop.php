<?php

namespace Gordy\BrainFix\Node\Structure;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node;
use Gordy\BrainFix\Type;

class WhileLoop implements Node\Structure
{
	use Node\HasToken;

	protected Node\Expression $condition;
	protected Node\Scope $body;

	public function __construct(Node\Expression $condition, Node\Scope $body, Token $token)
	{
		$this->condition = $condition;
		$this->body = $body;
		$this->token = $token;
	}

	public function compile(Environment $env) : void
	{
		$exprType = $this->condition->resultType($env);

		if ($this->condition instanceof Node\Expression\ScalarVariable)
		{
			$env->stream()->blockComment($this);

			$cell = $this->condition->memoryCell($env);

			$env->processor()->while($cell, function() use ($env) {
				$this->body->compile($env);
			}, "while $cell");
		}
		else if ($exprType instanceof Type\Computable && $exprType->numericCompatible())
		{
			if ($exprType->getNumeric() === 0)
			{
				// do nothing
			}
			else
			{
				throw new CompileError('infinite loop detected', $this->condition->token());
			}
		}
		else if ($exprType instanceof Type\Scalar)
		{
			$env->stream()->blockComment($this);

			$condition = $env->processor()->reserve();
			$this->condition->compileCalculation($env, $condition);
			$env->processor()->while($condition, function() use ($env, $condition) {
				$this->body->compile($env);
				$env->stream()->blockComment('recalculate condition');
				$env->processor()->unset($condition);
				$this->condition->compileCalculation($env, $condition);
			}, "while $condition");

			$env->processor()->release($condition);
		}
		else
		{
			throw new CompileError('scalar condition expected', $this->condition->token());
		}
	}

	public function __toString() : string
	{
		$expr = $this->condition;
		return "while ($expr)";
	}
}