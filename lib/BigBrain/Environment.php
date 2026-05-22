<?php

namespace Gordy\Brainfuck\BigBrain;

class Environment
{
	public function __construct(
		protected TrickyProcessor $trickyProcessor,
		protected Processor $processor,
		protected OutputStream $stream,
		protected Stack $stack,
		protected Memory $memory,
		protected ArraysMemory $arraysMemory,
		protected ArraysProcessor $arraysProcessor
	)
	{
	}

	public function processor() : Processor
	{
		return $this->processor;
	}

	public function stream() : OutputStream
	{
		return $this->stream;
	}

	public function memory() : Memory
	{
		return $this->memory;
	}

	public function arraysMemory() : ArraysMemory
	{
		return $this->arraysMemory;
	}

	public function arraysProcessor() : ArraysProcessor
	{
		return $this->arraysProcessor;
	}

	public function trickyProcessor() : trickyProcessor
	{
		return $this->trickyProcessor;
	}

	public function stack() : Stack
	{
		return $this->stack;
	}

	public static function makeForPrecompile(bool $uglify, int $trickySize, int $registrySize, int $memorySize, int $arraysMemorySize) : self
	{
		$rOffset = $trickySize;
		$mOffset = $rOffset + $registrySize;
		$amOffset = $mOffset + $memorySize;
		$stream = new OutputStream();
		$stack = new Stack();

		$processor = new Precompile\Processor($stream, $registrySize, $uglify, $rOffset);
		$trickyProcessor = new Precompile\TrickyProcessor($processor, $stream, $trickySize);
		$memory = new Precompile\Memory($stack, $stream, $mOffset);
		$arraysMemory = new Precompile\ArraysMemory($stack, $stream, $amOffset, $arraysMemorySize);

		$arraysProcessor = new ArraysProcessor($processor, $stream, $amOffset, $uglify);
		return new self($trickyProcessor, $processor, $stream, $stack, $memory, $arraysMemory, $arraysProcessor);
	}

	public static function makeForRelease(bool $uglify,  int $trickySize, int $registrySize, int $memorySize, int $arraysMemorySize) : self
	{
		$rOffset = $trickySize;
		$mOffset = $rOffset + $registrySize;
		$amOffset = $mOffset + $memorySize;
		$stream = new OutputStream();
		$stack = new Stack();

		$processor = new Processor($stream, $registrySize, $uglify, $rOffset);
		$trickyProcessor = new TrickyProcessor($processor, $stream, $trickySize);
		$memory = new Memory($stack, $stream, $mOffset);
		$arraysMemory = new ArraysMemory($stack, $stream, $amOffset, $arraysMemorySize);

		$arraysProcessor = new ArraysProcessor($processor, $stream, $amOffset, $uglify);
		return new self($trickyProcessor, $processor, $stream, $stack, $memory, $arraysMemory, $arraysProcessor);
	}
}