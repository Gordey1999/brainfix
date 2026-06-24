<?php

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_NOTICE & ~E_COMPILE_WARNING & ~E_DEPRECATED);


$request = json_decode(file_get_contents('php://input'), true);

if (!$request)
{
	http_response_code(400);
	echo 'request body is empty!';
	die;
}

$code = $request['code'] ?? '';
$lines = explode("\n", $code);

$version = 'current';

foreach ($lines as $line)
{
	if (trim($line) && trim($line)[0] !== '#')
	{
		break;
	}

	if (preg_match('/^\s*#\s*@version\s*[=:]?\s*([.\d]+)$/', $line, $matches))
	{
		$version = $matches[1];
	}
}

$versionsDir = __DIR__ . '/../previous_versions/';

if (file_exists($versionsDir . $version . '/compile.php'))
{
	require_once $versionsDir . $version . '/compile.php';
}
else
{
	require_once __DIR__ . '/../current/compile.php';
}