<?php

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_NOTICE & ~E_COMPILE_WARNING & ~E_DEPRECATED);

require_once $_SERVER['DOCUMENT_ROOT'] . '/brainfuck/lib/autoload.php';

$request = json_decode(file_get_contents('php://input'), true);

$img = imagecreatefrompng(__DIR__  . '/template.png');

$width = 50;
$height = 250;

$text = '';

$text = preg_replace('/[^+\-><\[\].,]/', '', $text);
$text = mb_str_split($text, 2);

$result = '';

for ($j = 0; $j < $height; $j++)
{
	for ($i = 0; $i < $width; $i++)
	{
		if (empty($text)) { break 2; }

		$isBlack = imagecolorat($img, $i, $j) === 0;

		if ($isBlack)
		{
			$result .= array_shift($text);
		}
		else
		{
			$result .= '  ';
		}
	}
	$result .= PHP_EOL;
}

if (!empty($text))
{
	while (true)
	{
		for ($i = 0; $i < $width; $i++)
		{
			if (empty($text)) { break 2; }

			$result .= array_shift($text);
		}
		$result .= PHP_EOL;
	}
}


?>

<pre>
<?= $result ?>
</pre>