<?php

namespace Gordy\BrainFix\Exception;

use Gordy\BrainFix\Parser\Token;

class CompileError extends Exception
{
	public function __construct(string $message, Token $token)
	{
		parent::__construct("COMPILE ERROR:\n$message", $token);
	}
}