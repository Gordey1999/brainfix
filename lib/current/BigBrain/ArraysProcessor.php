<?php

namespace Gordy\Brainfuck\BigBrain;

use Gordy\Brainfuck\BigBrain\Data\IndexData;
use Gordy\Brainfuck\BigBrain\Utils\Encoder;

class ArraysProcessor
{
	public const int CELL_SIZE = 2;
	public const int MAX_INDEX = 255;

	protected Processor $processor;
	protected OutputStream $stream;
	protected int $offset;
	protected bool $uglify;

	public function __construct(Processor $processor, OutputStream $stream, int $offset, bool $uglify)
	{
		$this->processor = $processor;
		$this->stream = $stream;
		$this->offset = $offset;
		$this->uglify = $uglify;
	}

	protected function initCell() : MemoryCell
	{
		return new MemoryCell($this->offset, 'adr_s');
	}

	public function dummyCell() : MemoryCell
	{
		return new MemoryCell($this->offset + 1, 'adr_d');
	}

	public function startCell() : MemoryCell
	{
		return new MemoryCell($this->offset + self::CELL_SIZE, 'i0');
	}

	public function carryCell() : MemoryCell
	{
		return new MemoryCell($this->offset + 2 * self::CELL_SIZE, 'i1');
	}

	public function initIndex(IndexData $index) : int
	{
		$leftOffset = 0;

		$staticOffset = $index->startOffset() + $index->computedOffset();

		if ($staticOffset + $index->maxDynamicOffset() <= self::MAX_INDEX)
		{
			$this->stream->startGroup("init static offset with `$staticOffset`");
			$this->processor->addConstant($this->startCell(), $staticOffset);
			$this->stream->endGroup();
		}
		else
		{
			if ($staticOffset <= self::MAX_INDEX)
			{
				$leftOffset = $staticOffset;
			}
			else if ($staticOffset % self::MAX_INDEX + $index->maxDynamicOffset() <= self::MAX_INDEX)
			{
				$nowOffset = $staticOffset % self::MAX_INDEX;
				$leftOffset = $staticOffset - $nowOffset;

				$this->stream->startGroup("init static offset with `$nowOffset`(`$leftOffset` in remainder)");
				$this->processor->addConstant($this->startCell(), $nowOffset);
				$this->stream->endGroup();
			}
			else
			{
				$leftOffset = $staticOffset;
			}
		}
		$this->processor->goto($this->startCell());

		return $leftOffset;
	}

	public function get(IndexData $index) : MemoryCell
	{
		$this->gotoTargetIndex($index);

		$this->stream->write('>', 'goto target cell');

		$this->stream->write('[-<+>>+<]<[->+<]+', 'copy carry');
		$this->stream->write('[->>[-<<+>>]<<<<]', 'move carry');

		// todo чуть быстрее для больших массивов 570 эл (805k => 687k)
		// >>[-[<<]>+>[>>]<<]
		// >-<<<[-<<]
		$this->processor->setPointer($this->initCell());

		return $this->startCell();
	}

	public function setConstant(IndexData $index, int $value) : void
	{
		$this->goto($index, function() use ($value) {
			$this->setCurrentByConstant($value);
		});
	}

	public function addConstant(IndexData $index, int $value) : void
	{
		$this->goto($index, function() use ($value) {
			$this->addConstantToCurrent($value);
		});
	}

	public function print(IndexData $index) : void
	{
		$this->goto($index, function() {
			$this->stream->write('.', 'print value');
		});
	}

	public function input(IndexData $index) : void
	{
		$this->goto($index, function() {
			$this->stream->write(',', 'input value');
		});
	}

	public function printString(IndexData $index, int $size) : void
	{
		$this->walk($index, $size, function() use (&$values) {
			$this->stream->write('.');
		}, 'print array');
	}

	public function inputString(IndexData $index) : void
	{
		$this->gotoIndex($index, function() {
			$this->stream->write(
				'+[>>>>,----------[++++++++++<<<[-]>>>[-<<<+>>>]<<+>>]<<]<<',
				'input until enter'
			);
		});
	}

	public function fill(IndexData $index, array $values) : void
	{
		$this->walk($index, count($values), function() use (&$values) {
			$value = array_shift($values);
			$this->setCurrentByConstant($value);
		});
	}

	public function set(IndexData $index) : void
	{
		$this->gotoMove($index, function() {
			$this->stream->write('[-]>[-<+>]<', 'set value');
		});
	}

	public function add(IndexData $index) : void
	{
		$this->gotoMove($index, function() {
			$this->stream->write('>[-<+>]<', 'add to value');
		});
	}

	public function sub(IndexData $index) : void
	{
		$this->gotoMove($index, function() {
			$this->stream->write('>[-<->]<', 'sub from value');
		});
	}

	protected function goto(IndexData $index, callable $callback) : void
	{
		$this->gotoTargetIndex($index);

		$this->stream->write('+>', 'goto target cell');

		$callback();

		$this->stream->write('<[-<<]', 'return to start');
		$this->processor->setPointer($this->initCell());
	}

	protected function gotoIndex(IndexData $index, callable $callback) : void
	{
		$this->gotoTargetIndex($index);

		$callback();

		$this->stream->write('[-<<]', 'return to start');
		$this->processor->setPointer($this->initCell());
	}

	protected function gotoTargetIndex(IndexData $index) : void
	{
		$leftOffset = $this->initIndex($index);
		$this->stream->write('[[->>+<<]+>>-]', "goto target index(`$leftOffset` in remainder)");

		// todo можно ходить сразу по 10 ячеек например.
		while ($leftOffset)
		{
			$nowOffset = min($leftOffset, self::MAX_INDEX);
			$leftOffset -= $nowOffset;

			$this->addConstantToCurrent($nowOffset);
			$this->stream->write('[[->>+<<]+>>-]', "goto target index(`$leftOffset` in remainder)");
		}
	}

	protected function gotoMove(IndexData $index, callable $callback) : void
	{
		$leftOffset = $this->initIndex($index);
		$this->stream->write('[>>[->>+<<]<<[->>+<<]+>>-]', "move carry to target index(`$leftOffset` in remainder)");

		while ($leftOffset)
		{
			$nowOffset = min($leftOffset, self::MAX_INDEX);
			$leftOffset -= $nowOffset;

			$this->addConstantToCurrent($nowOffset);
			$this->stream->write('[>>[->>+<<]<<[->>+<<]+>>-]', "move carry to target index(`$leftOffset` in remainder)");
		}

		$this->stream->write('+>', 'goto target value');

		$callback();

		$this->stream->write('<[-<<]', 'return to start');
		$this->processor->setPointer($this->initCell());
	}

	protected function walk(IndexData $index, int $count, callable $callback, string $groupComment = null) : void
	{
		$this->goto($index, function() use ($count, $callback, $groupComment) {
			if ($groupComment !== null)
			{
				$this->stream->startGroup($groupComment);
			}
			for ($i = 0; $i < $count; $i++)
			{
				$callback();
				if ($i < $count - 1)
				{
					$this->stream->write('>+>', 'goto next');
				}
			}
			if ($groupComment !== null)
			{
				$this->stream->endGroup();
			}
		});
	}

	protected function setCurrentByConstant(int $value) : void
	{
		$this->stream->write('[-]', 'unset value');
		$this->addConstantToCurrent($value);
	}

	protected function addConstantToCurrent(int $value) : void
	{
		$shortValue = Utils\ModuloHelper::normalizeConstant($value);

		if ($this->uglify && abs($shortValue) > 15)
		{
			[$a, $b, $c] = Utils\NumbersHelper::factorize(abs($shortValue));
			$c = $shortValue > 0 ? $c : -$c;

			$code = sprintf('>%s[-<%s>]<%s',
				Encoder::plus($a),
				$shortValue > 0 ? Encoder::plus($b) : Encoder::minus($b),
			    $c > 0 ? Encoder::plus($c) : Encoder::minus(-$c)
			);
		}
		else
		{
			$code = $shortValue > 0 ? Encoder::plus($shortValue) : Encoder::minus(-$shortValue);
		}

		if ($value > 0)
		{
			$this->stream->write($code, "add `$value` to current");
		}
		else
		{
			$value = -$value;
			$this->stream->write($code, "sub `$value` from current");
		}
	}
}