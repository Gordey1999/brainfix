<?php

namespace Gordy\Brainfuck\BigBrain\Parser;

class MetaParser
{
	public static function parseHeaders(string $code) : array
	{
		$headers = self::parseCode($code);

		$selfHeaders = [ 'title' => true ];

		$bfHeaders = array_diff($headers, $selfHeaders);

		return [$bfHeaders];
	}

	private static function parseCode(string $code) : array
	{
		$lines = explode("\n", $code);
		$result = [];

		foreach ($lines as $line)
		{
			if (trim($line) && trim($line)[0] !== '#')
			{
				break;
			}

			if (preg_match('/^\s*#\s*@([a-zA-Z][^\s=:]*)\s*[=:]?\s*(.*)$/', $line, $matches))
			{
				$result[mb_strtolower($matches[1])] = $matches[2];
			}
		}

		return $result;
	}
}