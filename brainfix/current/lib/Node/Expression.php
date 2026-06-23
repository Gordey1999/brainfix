<?php

namespace Gordy\BrainFix\Node;

use Gordy\BrainFix\Environment;
use Gordy\BrainFix\MemoryCell;
use Gordy\BrainFix\Type;

interface Expression extends Node
{
	public function resultType(Environment $env) : Type\Type;

	public function compileCalculation(Environment $env, MemoryCell $result) : void;

	public function hasVariable(string $name) : bool; // todo remove
}