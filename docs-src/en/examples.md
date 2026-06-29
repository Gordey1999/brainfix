# Brainfix Code Examples

> This documentation is automatically translated by an AI language model. If you notice any inaccuracies or prefer the original text, you can switch to the **[Russian Version](../)**.

This section contains a collection of practical, ready-to-run code examples written in Brainfix. They demonstrate how to use the language syntax to solve real-world problems, show how input/output, arrays, and compiler optimizations work in practice, and illustrate how to bypass the architectural limitations of the target Brainfuck platform.

Each example comes with a detailed logic breakdown and inline code comments to help you understand the nuances of writing efficient programs.

## Multiplication Table

This example demonstrates how to work with nested loops (`while` and `for`), leverage a prefix increment directly within a loop condition, and print complex compound expressions to the screen. The program outputs a classic multiplication table for numbers ranging from 2 to 9.

### Source Code

```brainfix
# @title: Multiplication

byte i = 1;

while (++i < 10) {
    for (byte j = 2; j < 10; j++)
        out i, ' * ', j, ' = ', i * j, eol;

    out eol;
}
```

### Logic Breakdown

1. **Row Counter Initialization (`byte i = 1;`)**: A variable for the outer loop is created. We start from one because the increment occurs directly inside the loop condition before the first iteration begins.
2. **Outer Loop (`while (++i < 10)`)**: A prefix increment `++i` is utilized here. This means that upon the very first entry into the loop, the value of `i` is increased to `2` first, and only then is the condition `2 < 10` evaluated. The loop will continue running until `i` reaches `10`.
3. **Inner Loop (`for (byte j = 2; j < 10; j++)`)**: For each row `i`, a loop runs with the variable `j` from 2 to 9. Note that the curly braces `{}` are omitted here because the loop body contains exactly one `out` command.
4. **Combined Output (`out ...`)**: In a single call, the `out` command accepts a chain of variables, character literals, and the mathematical expression `i * j`. The `eol` keyword is appended at the end of the line to trigger a carriage return/newline.
5. **Block Separation (`out eol;`)**: After the inner loop prints an entire block for the current number `i`, the outer loop prints an empty `eol` line to visually separate the table blocks.


## String Transliteration

This comprehensive example demonstrates how to work with two-dimensional string arrays, handle stream-based input, perform arithmetic operations on character codes (ASCII/Windows-1251), and convert characters to uppercase. The program replaces Cyrillic letters with their Latin counterparts.

### Source Code

```brainfix
# @title: Translit

char map[][] = [
  'a', 'b', 'v', 'g', 'd', 'e', 'zh', 'z', 'i', 'y',
  'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u',
  'f', 'h', 'c', 'ch', 'sh', 'shch', '', 'y', '', 'e',
  'yu', 'ya'
];

char letter;

do
{
    in letter;

    if (letter >= 'а' && letter <= 'я')
    {
        out map[letter - 'а'];
    }
    else if (letter >= 'А' && letter <= 'Я')
    {
        for (byte i = 0; i < sizeof map[0]; i++)
        {
            if (map[letter - 'А'][i])
            {
                char ch = map[letter - 'А'][i] - ('a' - 'A');
                out ch;
            }
        }
    }
    else
    {
        out letter;
    }
}
while (letter != eol);
```

### Logic Breakdown

1. **Creating the Translation Lookup Table (`char map[][]`)**: A two-dimensional string array is declared. The compiler automatically determines its size as `32x4` (matching the 32 letters of the Russian alphabet, excluding "ё", where the longest Latin equivalent is 4 characters for `'shch'`). Empty quotes `''` represent the hard and soft signs ("ъ" and "ь"), which are omitted during transliteration.
2. **Stream-based Reading (`do { in letter; ... } while (letter != eol);`)**: The `do/while` loop guarantees that the program reads and processes at least one character. Reading occurs character by character until a newline character (`eol`) is encountered.
3. **Handling Lowercase Letters (`letter >= 'а' && letter <= 'я'`)**:
    * The exact letter index within the array is calculated by subtracting the character code of the alphabet's first letter: `letter - 'а'`.
    * Using `out map[...]`, the entire transliteration string for that letter is printed to the screen. Thanks to multi-dimensional array optimization, this jump using a dynamic index executes much faster than it would in a flat array.
4. **Handling Uppercase Letters (`letter >= 'А' && letter <= 'Я'`)**:
    * Since all strings inside the `map` array are stored in lowercase, uppercase letters must be converted manually.
    * A `for` loop iterates through the characters of the transliteration string. The string's maximum length is restricted by the size of the array's second dimension (`sizeof map[0]`, which equals 4 iterations).
    * The condition `if (map[...][i])` filters out empty cells (zeros that the compiler automatically used to pad shorter strings like `'a'`).
    * The mathematical expression `map[...][i] - ('a' - 'A')` subtracts the encoding offset between lowercase and uppercase letters, converting the current character to uppercase. Since explicit type casting is unavailable, the result of this operation is first stored in an intermediate `char ch` variable, which is then printed.
5. **Handling Other Characters (`else { out letter; }`)**: Punctuation marks, spaces, digits, and Latin letters are sent back to the output stream completely unmodified.


## Brainfuck Interpreter in Brainfix

This example demonstrates how to build a fully functional Brainfuck interpreter. The program parses a source code string, emulates a classic memory tape, manages its data pointer, and processes nested `[` and `]` loops by shifting the execution index across the instruction array.

### Source Code

```brainfix
# @title: Translator

char code[256];
char memory[100];
byte pointer, level;

in code;

for (byte i = 0; i < sizeof code; i++)
{
    char command = code[i];

    if (command == '>')
    {
        pointer++;
    }
    else if (command == '<')
    {
        pointer--;
    }
    else if (command == '+')
    {
        memory[pointer]++;
    }
    else if (command == '-')
    {
        memory[pointer]--;
    }
    else if (command == '.')
    {
        out memory[pointer];
    }
    else if (command == ',')
    {
        in memory[pointer];
    }
    else if (command == '[' && !memory[pointer])
    {
        level++;
        while (level)
        {
            i++;
            if      (code[i] == '[') level++;
            else if (code[i] == ']') level--;
        }
    }
    else if (command == ']' && memory[pointer])
    {
        level++;
        while (level)
        {
            i--;
            if      (code[i] == '[') level--;
            else if (code[i] == ']') level++;
        }
        i--;
    }
}
```

### Logic Breakdown

1. **Allocating Memory for the Virtual Machine**:
    * `char code[256]` — A buffer to store the raw Brainfuck source code being read.
    * `char memory[100]` — An emulated memory tape for the executed Brainfuck program. The entire tape is initially zero-initialized.
    * `byte pointer` — A pointer targeting the active cell on the emulated `memory` tape.
    * `byte level` — A nesting level counter used to correctly track matched brackets during loops.
2. **Reading the Source Code (`in code;`)**: The command reads the entire Brainfuck code string entered by the user directly into the `code` array.
3. **The Interpreter's Main Loop (`for ... sizeof code`)**: The program sequentially iterates through the `code` array index by index, extracting the active instruction into the `command` variable.
4. **Handling Basic Instructions (`>`, `<`, `+`, `-`, `.`, `,`)**:
    * Shifting instructions `>` and `<` increment and decrement the virtual `pointer`.
    * Modification instructions `+` and `-` alter the value stored at `memory[pointer]`.
    * I/O operations `.` and `,` pipe data directly from the virtual tape to the actual environment execution stream.
5. **Emulating Loop Start (`[` when the current cell equals 0)**:
    * If the active memory cell contains zero, the loop body must be skipped, and the interpreter needs to jump forward to the matching closing bracket `]`.
    * The `level` variable is incremented to 1. Inside the `while (level)` loop, the index `i` moves forward through the code array. If a nested `[` is found along the way, `level` increases; if a `]` is found, `level` decreases. As soon as `level` drops back to 0, the matching bracket is found, and execution resumes from the subsequent instruction.
6. **Emulating Loop End (`]` when the current cell is NOT 0)**:
    * If the active memory cell is non-zero, the program execution flow must jump backward to the loop start (the matching opening bracket `[`).
    * The search logic mirrors the forward jump: `level` is incremented, and the index `i` steps backward (`i--`) until the nesting counter hits 0 again.
    * Upon exiting the `while (level)` block, the instruction pointer `i` lands precisely on the matching `[` character. To prevent the upcoming `i++` step of the main `for` loop from skipping the very first command inside the loop body, a mandatory manual rollback `i--` is performed.


## Where to Find More Examples?

More complex, interactive, and engaging programs (including full-featured games like "Minesweeper" and "Nim") can be explored, analyzed, and run live directly inside the **Brainfix Studio** web IDE. There, you can also write, test, and compile your own custom code.

* **Official Online IDE:** [Brainfix Studio](https://gordey1999.github.io/brainfix/)
