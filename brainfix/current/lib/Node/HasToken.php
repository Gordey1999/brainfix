<?php

namespace Gordy\BrainFix\Node;

use Gordy\BrainFix\Parser\Token;

trait HasToken
{
	protected Token $token;

	public function token() : Token
	{
		return $this->token;
	}
}