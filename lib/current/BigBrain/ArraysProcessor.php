<?php

namespace Gordy\Brainfuck\BigBrain;

use Gordy\Brainfuck\BigBrain\Utils\Encoder;

class ArraysProcessor
{
	public const int CELL_SIZE = 2;
	public const int MAX_INDEX = 255;

	public const string POINTER_STATUS_FREE = 'free'; // за пределами массива
	public const string POINTER_STATUS_INDEX = 'index'; // на ячейке с индексом

	protected Processor $processor;
	protected MemoryPointer $pointer;
	protected OutputStream $stream;
	protected int $offset;
	protected bool $uglify;

	protected string $pointerStatus = self::POINTER_STATUS_FREE;

	public function __construct(Processor $processor, MemoryPointer $pointer, OutputStream $stream, int $offset, bool $uglify)
	{
		$this->processor = $processor;
		$this->pointer = $pointer;
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
		return new MemoryCell($this->offset + 1, 'adr_d');
	}

	public function calculateIndex(int $multiplier) : void
	{
		$this->gotoStart();

		$incr = '+>>';
		$this->stream->write(sprintf(
			'[[-%s+%s]%s-]',
			Encoder::moveForward($multiplier * 2),
			Encoder::moveBack($multiplier * 2),
			str_repeat($incr, $multiplier)
		), "calculate target index(x$multiplier)");

		$this->pointerStatus = self::POINTER_STATUS_INDEX;
		$this->pointer->setLostState($this->freePointer(...));
	}

	public function calculateAddedIndex(int $multiplier) : void
	{
		$this->moveCarryToNextIndex();
		$this->moveNextIndexToIndex();

		$incr = '+>>';
		$this->stream->write(sprintf(
			'[[-%s+%s]%s-]',
			Encoder::moveForward($multiplier * 2),
			Encoder::moveBack($multiplier * 2),
			str_repeat($incr, $multiplier)
		), "calculate target index(x$multiplier)");

		$this->pointerStatus = self::POINTER_STATUS_INDEX;
		$this->pointer->setLostState($this->freePointer(...));
	}

	public function computeIndex(int $offset, bool $firstIndex = true) : void
	{
		if ($offset === 0) { return; }

		if ($firstIndex)
		{
			$this->gotoStart();
		}
		else
		{
			$this->gotoIndex();
		}

		$this->stream->write(str_repeat('+>>', $offset), "calculate target index(+$offset)");

		$this->pointerStatus = self::POINTER_STATUS_INDEX;
		$this->pointer->setLostState($this->freePointer(...));
	}

	public function moveCarryToValue() : void
	{
		$this->gotoCarry();
		$this->stream->write('[->[>>]>+<<<[<<]>]', 'add carry to value');
	}

	public function subCarryFromValue() : void
	{
		$this->gotoCarry();
		$this->stream->write('[->[>>]>-<<<[<<]>]', 'sub carry from value');
	}

	public function getValue() : MemoryCell
	{
		$this->gotoIndex();

		$this->stream->write('>[->+<]>', 'copy carry');
		$this->stream->write('[-<+<<<[<<]>+>[>>]>>]', 'move carry');
		$this->stream->write('<<', 'goto target index');

		return $this->carryCell();
	}

	public function takeValue() : MemoryCell
	{
		$this->gotoIndex();

		$this->stream->write('>[-<<<[<<]>+>[>>]>]', 'move value to start');
		$this->stream->write('<', 'goto target index');

		return $this->carryCell();
	}

	public function setConstant(int $value) : void
	{
		$this->goto(function() use ($value) {
			$this->stream->startGroup("set target value with `$value`");
			$this->stream->write('[-]', 'unset value');
			$this->addConstantToCurrent($value);
			$this->stream->endGroup();
		});
	}

	public function addConstant(int $value) : void
	{
		$this->goto(function() use ($value) {
			$this->stream->startGroup("set target value with `$value`");
			$this->addConstantToCurrent($value);
			$this->stream->endGroup();
		});
	}

	public function print() : void
	{
		$this->goto(function() {
			$this->stream->write('.', 'print value');
		});
	}

	public function input() : void
	{
		$this->goto(function() {
			$this->stream->write(',', 'print value');
		});
	}

	public function printString(int $size) : void
	{
		$this->walk($size, function() use (&$values) {
			$this->stream->write('.');
		}, 'print array');
	}

	public function inputString() : void
	{
		$this->gotoIndex();
		$this->stream->write(
			'+[>>>>,----------[++++++++++<<<[-]>>>[-<<<+>>>]<<+>>]<<]<<',
			'input until enter'
		);
		$this->clearIndex();
	}

	public function fill(array $values) : void
	{
		$this->walk(count($values), function() use (&$values) {
			$value = array_shift($values);
			$this->stream->write('[-]', 'unset value');
			$this->addConstantToCurrent($value);
		});
	}

	public function unsetValue() : void
	{
		$this->gotoIndex();
		$this->stream->write('>[-]<', 'unset value');
	}

	public function add() : void
	{
		$this->moveCarryToValue();
		$this->clearIndex();
	}

	public function sub() : void
	{
		$this->subCarryFromValue();
		$this->clearIndex();
	}

	protected function gotoStart() : void
	{
		$this->processor->goto($this->startCell());
	}

	protected function gotoCarry() : void
	{
		$this->processor->goto($this->carryCell());
	}

	protected function gotoIndex() : void
	{
		if ($this->pointerStatus === self::POINTER_STATUS_INDEX) { return; }

		$this->gotoStart();

		$this->stream->write('[>>]', "goto target index");

		$this->pointerStatus = self::POINTER_STATUS_INDEX;
		$this->pointer->setLostState($this->freePointer(...));
	}

	protected function freePointer() : int
	{
		if ($this->pointerStatus === self::POINTER_STATUS_INDEX)
		{
			$this->stream->write('<<[<<]', 'return to start');
			$this->pointerStatus = self::POINTER_STATUS_FREE;
		}

		return $this->initCell()->address();
	}

	public function clearIndex() : void
	{
		$this->gotoIndex();
		$this->stream->write('<<[-<<]', 'clear index');
		$this->pointerStatus = self::POINTER_STATUS_FREE;
	}

	protected function moveCarryToNextIndex() : void
	{
		$this->gotoCarry();
		$this->stream->write('[->[>>]>>+<<<<[<<]>]', 'carry value');
	}

	protected function moveNextIndexToIndex() : void
	{
		$this->gotoIndex();
		$this->stream->write('>>[-<<+>>]<<', 'copy carry to index');
	}

	protected function goto(callable $callback) : void
	{
		$this->gotoIndex();
		$this->stream->write('>', 'goto target cell');

		$callback();

		$this->stream->write('<', 'goto target index');
		$this->clearIndex();
	}

	protected function walk(int $count, callable $callback, string $groupComment = null) : void
	{
		if ($count === 0) { return; }
		$this->gotoIndex();

		$this->stream->write('+>', 'goto target cell');

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

		$this->stream->write('>', 'goto target index');
		$this->clearIndex();
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