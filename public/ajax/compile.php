<?php

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_NOTICE & ~E_COMPILE_WARNING & ~E_DEPRECATED);


$request = json_decode(file_get_contents('php://input'), true);

$code = $request['code'] ?? '';
$lines = explode("\n", $code);

$version = 'current';

foreach ($lines as $line)
{
	if (trim($line) && trim($line)[0] !== '#')
	{
		break;
	}

	if (preg_match('/^\s*#\s*@version\s*[=:]?\s*(.*)$/', $line, $matches))
	{
		$version = $matches[1];
	}
}

$rootDir = $_SERVER['DOCUMENT_ROOT'] . '/brainfuck/lib/';

if (file_exists($rootDir . $version . '/compile.php'))
{
	require_once $rootDir . $version . '/compile.php';
}
else
{
	require_once $rootDir . 'current/compile.php';
}