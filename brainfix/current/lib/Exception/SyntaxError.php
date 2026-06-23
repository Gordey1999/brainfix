<?php

namespace Gordy\BrainFix\Exception;

use Gordy\BrainFix\Parser\Token;

class SyntaxError extends Exception
{
	public function __construct(string $message, Token $token)
	{
		parent::__construct("SYNTAX ERROR:\n$message", $token);
	}
}