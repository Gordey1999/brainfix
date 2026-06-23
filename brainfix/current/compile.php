<?php

require_once __DIR__ . '/autoload.php';

use Gordy\BrainFix;
use Gordy\BrainFix\Parser\MetaParser;

$request = json_decode(file_get_contents('php://input'), true);

$log = "version: 1.1\n\n";

$debug = false;

try
{
	$uglify = $request['uglify'];
	$code = $request['code'];

	[ $commentLevel, $bfHeaders ] = MetaParser::parseHeaders($code);

	$tokens = BrainFix\Parser\TokenSplitter::parse($code);
	$tokenStream = new BrainFix\Parser\TokenStream($tokens);
	$parser = new BrainFix\Parser\Parser($tokenStream);
	$program = $parser->parse();

	$precompileEnv = BrainFix\Environment::makeForPrecompile($uglify, 100, 500, 5000);
	$program->compile($precompileEnv);

	$registrySize = $precompileEnv->processor()->computedRegistrySize();
	$memorySize = $precompileEnv->memory()->computedMemorySize();
	$arraysMemorySize = $precompileEnv->arraysMemory()->computedMemorySize();

	$log .= "registry size computed: $registrySize\n";
	$log .= "stack size computed: $memorySize\n";
	$log .= "arrays stack size computed: $arraysMemorySize\n";

	$env = BrainFix\Environment::makeForRelease($uglify, $registrySize, $memorySize, $arraysMemorySize);

	$program->compile($env);

	if ($request['min'])
	{
		$env->stream()->setHeaders([ 'title' => '.min.bf' ] + $bfHeaders);
		$result = $env->stream()->buildMin();
	}
	else
	{
		$env->stream()->setCommentLevel($commentLevel);
		$env->stream()->setHeaders([ 'title' => '.bf' ] + $bfHeaders);
		$result = $env->stream()->build();
	}

	$codeLength = $env->stream()->codeLength();
	$log .= "finished! code length: $codeLength\n";

	$result = [
		'status' => 'ok',
		'result' => $result,
		'log' => $log,
	];
}
catch (BrainFix\Exception\Exception $e)
{
	$token = $e->getToken();

	$message = $debug ?
		sprintf(
			"%s\n\n\n\ndebug info:\n%s(%s)\n\ntrace:\n%s",
			$e->getMessage(),
			$e->getFile(),
			$e->getLine(),
			$e->getTraceAsString()
		)
		: $e->getMessage();

	$result = [
		'status' => 'error',
		'message' => $message,
		'position' => [
			'start' => $token->index(),
			'length'  => mb_strlen($token->value()),
		],
	];
}
catch (\Throwable $e)
{
	echo sprintf(
		"%s\n%s(%s)\ntrace:\n%s",
		$e->getMessage(),
		$e->getFile(),
		$e->getLine(),
		$e->getTraceAsString()
	);
	die;
}

echo json_encode($result);