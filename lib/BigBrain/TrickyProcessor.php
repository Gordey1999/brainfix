<?php

namespace Gordy\Brainfuck\BigBrain;

class TrickyProcessor
{
	public const string T_DVD = 'T_DVD';
	public const string T_REM = 'T_REM';
	public const string T_RES = 'T_RES';
	public const string T_DM1 = 'T_DM1';
	public const string T_DM2 = 'T_DM2';

	protected int $pointer = 0;
	protected int $registrySize;
	protected Processor $processor;

	/** @var MemoryCell[] */
	protected array $cells;

	protected OutputStream $stream;

	public function __construct(Processor $processor, OutputStream $stream, int $registrySize)
	{
		$this->stream = $stream;
		$this->registrySize = $registrySize;
		$this->processor = $processor;

		$this->cells = [
			self::T_DVD => new MemoryCell(0, self::T_DVD),
			self::T_REM => new MemoryCell(1, self::T_REM),
			self::T_RES => new MemoryCell(2, self::T_RES),
			self::T_DM1 => new MemoryCell(3, self::T_DM1),
			self::T_DM2 => new MemoryCell(4, self::T_DM2),
		];

		if ($registrySize > 0)
		{
			foreach ($this->cells as $cell)
			{
				$this->stream->memoryComment($cell->address(), $cell->label());
			}
		}
	}

	protected function _divide(MemoryCell $a, MemoryCell $result, MemoryCell $remainder) : void
	{
		$proc = $this->processor;

		$proc->while($a, function () use ($a, $proc) {
			$proc->decrement($a);
			$proc->goto($this->getCell(self::T_DVD));
			$this->stream->write('-[>+>>]>[+[-<+>]>+>>]<<<<', 's o m e   m a g i c');
		}, 'division cycle');

		$proc->unset($this->getCell(self::T_DVD));
		$proc->move($this->getCell(self::T_REM), $remainder);
		$proc->move($this->getCell(self::T_RES), $result);
	}

	public function divide(MemoryCell $a, MemoryCell $b, MemoryCell $result, MemoryCell $remainder) : void
	{
		$this->processor->move($b, $this->getCell(self::T_DVD));
		$this->_divide($a, $result, $remainder);
	}

	public function divideByConstant(MemoryCell $a, int $const, MemoryCell $result, MemoryCell $remainder) : void
	{
		$this->processor->addConstant($this->getCell(self::T_DVD), $const);
		$this->_divide($a, $result, $remainder);
	}

	protected function _isMore(MemoryCell $b, MemoryCell $result) : void
	{
		$proc = $this->processor;

		$proc->while($b, function () use ($b, $proc) {
			$proc->decrement($b);
			$proc->increment($this->getCell(self::T_REM));
			$proc->goto($this->getCell(self::T_DVD));
			$this->stream->write('[->>>]>[>>>]<<<<', 'm o r e   m a g i c');
		}, 'isMore cycle');

		$proc->decrement($this->getCell(self::T_REM));
		$proc->move($this->getCell(self::T_DVD), $result);
	}

	public function isMore(MemoryCell $a, MemoryCell $b, MemoryCell $result) : void
	{
		$this->processor->move($a, $this->getCell(self::T_DVD));
		$this->_isMore($b, $result);
	}

	public function isMoreThenConstant(MemoryCell $a, int $const, MemoryCell $result) : void
	{
		// todo
	}

	public function printNumber(MemoryCell $number) : void
	{
		$proc = $this->processor;

		[ $a, $b, $c ] = $proc->reserveSeveral(3, $number);

		$this->divideByConstant($number, 10, $number, $a);
		$proc->copy($number, $b);
		$proc->addConstant($c, 9);
		$this->isMore($b, $c, $b);

		$proc->if($b, function () use ($proc, $number, $b) {
			$this->divideByConstant($number, 10, $number, $b);
			$proc->addConstant($number, 48);
			$proc->print($number);
			$proc->unset($number);
			$proc->addConstant($b, 48);
			$proc->print($b);
			$proc->unset($b);
		}, 'if 3 digit number');

		$proc->while($number, function() use ($proc, $number) {
			$proc->addConstant($number, 48);
			$proc->print($number);
			$proc->unset($number);
		}, "if 2 digit number");

		$proc->addConstant($a, 48);
		$proc->print($a);
		$proc->unset($a);

		$proc->release($a, $b, $c);
	}

	public function getCell(string $name) : MemoryCell
	{
		return $this->cells[$name];
	}
}