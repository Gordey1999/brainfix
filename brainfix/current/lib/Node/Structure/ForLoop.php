<?php

namespace Gordy\BrainFix\Node\Structure;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node;
use Gordy\BrainFix\Type;

class ForLoop implements Node\Structure
{
	use Node\HasToken;

	public function __construct(
		protected Node\Node $init,
		protected Node\Expression $condition,
		protected Node\Node $increment,
		protected Node\Scope $body,
		protected Token $token)
	{
	}

	public function compile(Environment $env) : void
	{
		$env->stack()->newScope();

		$env->stream()->blockComment($this);
		$this->init->compile($env);

		$conditionType = $this->condition->resultType($env);

		if ($this->condition instanceof Node\Expression\ScalarVariable)
		{
			$cell = $this->condition->memoryCell($env);

			$env->processor()->while($cell, function() use ($env) {
				$this->body->compile($env);
				$this->increment->compile($env);
			}, "while $cell");
		}
		else if ($conditionType instanceof Type\Computable && $conditionType->numericCompatible())
		{
			if ($conditionType->getNumeric() === 0)
			{
				// do nothing
			}
			else
			{
				throw new CompileError('infinite loop detected', $this->condition->token());
			}
		}
		else if ($conditionType instanceof Type\Scalar)
		{
			$condition = $env->processor()->reserve();
			$this->condition->compileCalculation($env, $condition);
			$env->processor()->while($condition, function() use ($env, $condition) {
				$this->body->compile($env);

				$env->stream()->blockComment('increment');
				$this->increment->compile($env);

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
		$env->stack()->dropScope();
	}

	public function __toString() : string
	{
		return "for ({$this->init}; {$this->condition}; {$this->increment})";
	}
}