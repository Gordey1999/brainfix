<?php

namespace Gordy\Brainfuck\BigBrain\Parser;

use Gordy\Brainfuck\BigBrain\OutputStream;

class MetaParser
{
	public static function parseHeaders(string $code) : array
	{
		$headers = self::parseCode($code);

		$commentLevel = self::parseCommentLevel($headers['comment_level'] ?? '');

		$selfHeaders = [ 'comment_level' => true ];
		$bfHeaders = array_diff_key($headers, $selfHeaders);

		return [$commentLevel, $bfHeaders];
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

	private static function parseCommentLevel(string $value) : int
	{
		return match (mb_strtolower($value))
		{
			'source' => OutputStream::COMMENT_LEVEL_SOURCE,
			'memory' => OutputStream::COMMENT_LEVEL_MEMORY,
			'none' => OutputStream::COMMENT_LEVEL_NONE,
			default => OutputStream::COMMENT_LEVEL_FULL,
		};
	}
}