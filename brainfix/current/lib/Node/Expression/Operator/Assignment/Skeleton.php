<?php

namespace Gordy\BrainFix\Node\Expression\Operator\Assignment;

use Gordy\BrainFix;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Exception\CompileError;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Node\HasToken;
use Gordy\BrainFix\Type;

abstract class Skeleton implements Expression
{
	use HasToken;

	protected Expression\Assignable $to;
	protected Expression $value;

	public function __construct(Expression $to, Expression $expr, Token $token)
	{
		if (!$to instanceof Expression\Assignable)
		{
			throw new CompileError('assignable value expected', $to->token());
		}

		$this->to = $to;
		$this->value = $expr;
		$this->token = $token;
	}

	public function resultType(Environment $env) : Type\Type
	{
		return $this->to->resultType($env);
	}

	public function compile(BrainFix\Environment $env) : void
	{
		$env->stream()->blockComment($this);
		$this->assign($env);
	}

	protected abstract function assign(Environment $env) : void;

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		$this->assign($env);
		$this->to->compileCalculation($env, $result);
	}

	public function hasVariable(string $name) : bool
	{
		return $this->value->hasVariable($name);
	}
}