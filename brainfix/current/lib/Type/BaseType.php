<?php

namespace Gordy\BrainFix\Type;

interface BaseType extends Type
{
	public function __toString(): string;
}