<?php

namespace Gordy\BrainFix\Node\Expression\Operator;

use Gordy\BrainFix;
use Gordy\BrainFix\Environment;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Parser\Token;
use Gordy\BrainFix\Node\Expression;
use Gordy\BrainFix\Type;
use Gordy\BrainFix\Exception\CompileError;

class Comma implements Expression
{
	use BrainFix\Node\HasToken;

	protected Expression $left;
	protected Expression $right;

	public function __construct(Expression $left, Expression $right, Token $token)
	{
		$this->left = $left;
		$this->right = $right;
		$this->token = $token;
	}

	public function compile(BrainFix\Environment $env) : void
	{
		$this->left->compile($env);
		$this->right->compile($env);
	}

	/** @return Expression[] */
	public function list() : array
	{
		$result = [];
		if ($this->left instanceof self)
		{
			$result = array_merge($result, $this->left->list());
		}
		else
		{
			$result[] = $this->left;
		}

		if ($this->right instanceof self)
		{
			$result = array_merge($result, $this->right->list());
		}
		else
		{
			$result[] = $this->right;
		}

		return $result;
	}

	public function resultType(Environment $env) : Type\Type
	{
		throw new CompileError('unexpected operator ","', $this->token);
	}

	public function compileCalculation(Environment $env, MemoryCell $result) : void
	{
		throw new \Exception('not implemented');
	}

	public function hasVariable(string $name) : bool
	{
		return $this->left->hasVariable($name) || $this->right->hasVariable($name);
	}

	public function __toString() : string
	{
		return sprintf('%s, %s', $this->left, $this->right);
	}
}