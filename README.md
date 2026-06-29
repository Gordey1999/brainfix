**English** | [Русский](README.ru.md)

# Brainfix

**Brainfix** is a C-like, statically typed, high-level programming language designed specifically to compile into ultra-compact and highly optimized **Brainfuck** code.

The project aims to solve the core issue of esoteric programming: the extreme difficulty of writing, reading, and debugging algorithms in pure Brainfuck. The language provides familiar abstractions (variables, multi-dimensional arrays, loops, and conditionals) while handling all the tedious, low-level memory manipulation under the hood.

![Brainfix Code Example](docs/wiki/assets/img/brainfix-preview.png)

**Key Features**
* **C-like Syntax:** Forget the chaotic mess of `><+-`. Use familiar variables, multi-dimensional arrays, loops (`for`, `while`, `do-while`), and conditionals (`if-else`).
* **Easy Data Handling:** Native support for strings and simplified numerical input/output.
* **Extreme Optimization:** The smart compiler analyzes your code and generates the shortest, fastest possible instruction sequences for Brainfuck.
* **Sanity Saver:** The name speaks for itself. This project is built to let you write complex Brainfuck programs while keeping your sanity intact.

[![Read the Documentation](docs/wiki/assets/img/btn-docs-en.svg)](https://gordey1999.github.io/brainfix/wiki/)

## Ecosystem & Web IDE (Brainfix Studio)

The language comes with a fully-featured, browser-based development environment.
No installation required — you can write, compile, test, and run your code directly in a user-friendly editor.

![Brainfix Studio Interface](docs/wiki/assets/img/studio-preview.png)

**IDE Features:**
* **Two Editors in One:** Write code in `Brainfix (.bfx)` or switch instantly to pure `Brainfuck (.bf)`.
* **Built-in Compiler:** Transform your `.bfx` code into `.bf` instructions with a single click.
* **Built-in Interpreter:** Run complex `.bf` programs and interact with them via an intuitive terminal interface.
* **Visual Debugger:** Track every program step and monitor memory cell values in real time to find and fix bugs faster.

[![Launch Online Editor](docs/wiki/assets/img/btn-ide.svg)](https://gordey1999.github.io/brainfix/)

## Projects Built with Brainfix

To showcase the capabilities of the language and its compiler, several fully interactive projects have been written entirely in Brainfix:

* **Minesweeper** — The classic puzzle game featuring grid generation, flag placement, and cell revealing.
* **Nim** — A mathematical strategy game where you play against an AI opponent.

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="50%" align="center">
      <img src="docs/wiki/assets/img/saper.gif" alt="Minesweeper Gameplay Demo" />
    </td>
    <td width="50%" align="center">
      <img src="docs/wiki/assets/img/nim.gif" alt="Nim Gameplay Demo" />
    </td>
  </tr>
</table>

These programs (and more) come pre-loaded in the examples menu inside **Brainfix Studio**. You can open them instantly and use them as boilerplate templates to explore the language syntax.