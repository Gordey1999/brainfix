<?php

namespace Gordy\BrainFix\Node;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\Parser\Token;

interface Node
{
	public function compile(Environment $env) : void;

	public function token() : Token;

	public function __toString() : string;
}