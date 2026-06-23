<?php

namespace Gordy\BrainFix\Exception;

use Gordy\BrainFix\Parser\Token;

class ParseError extends Exception
{
	public function __construct(string $message, Token $token)
	{
		parent::__construct("PARSE ERROR:\n$message", $token);
	}
}