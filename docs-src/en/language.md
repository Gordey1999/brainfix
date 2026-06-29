# Brainfix Syntax Documentation

> This documentation is automatically translated by an AI language model. If you notice any inaccuracies or prefer the original text, you can switch to the **[Russian Version](../)**.

**Brainfix** is a C-like, statically typed programming language designed specifically for developing programs in Brainfuck. This page contains a comprehensive syntax guide that will help you explore the language in detail and start writing your own programs.

## Your First Program

The classic "Hello, world!" example in Brainfix takes only a single line of code:

```brainfix
out 'Hello, world!';
```

When compiled into Brainfuck, the program generates 188 instructions:
```brainfuck
>++++++++[-<+++++++++>]<.>++++[-<+++++++>]<+.+++++++..+++.>++++++[-<----------->]<-.------------.>+++++[-<+++++++++++>]<.>++++[-<++++++>]<.+++.------.--------.>++++++[-<----------->]<-.[-]
```

### Semicolons

In Brainfix, the semicolon `;` acts as a statement terminator. It must be placed at the end of every complete statement (such as variable declarations, assignments, and input/output commands).

Omitting a semicolon will result in a compilation error. The only place where a `;` is not required is right after a closing curly brace `}` of a code block.

```brainfix
byte age = 20; # Correct: the statement is terminated with a semicolon
out age;       # Correct

byte x = 1     # Compilation error: missing semicolon!
```

### Comments

Comments are used to explain the program's logic and are completely ignored by the compiler. They help make your source code readable and maintainable.

Brainfix supports **single-line comments** only. They begin with the hash symbol `#`. Everything written after this symbol up to the end of the current line is treated as a comment.

```brainfix
# This is a full-line comment
byte speed = 100; # This is a comment placed after a line of code

# The # character inside a string literal is not treated as a comment:
out "The # symbol here will be printed as regular text";
```

## Variables and Data Types

Variable names in Brainfix are case-sensitive. They can consist of Latin letters, numbers, underscores `_`, and dollar signs `$`, but they cannot begin with a digit.

```brainfix
# Valid names:
a, b, char1, firstNumber, \$a, _B, count, COUNT
```

Brainfix is a statically typed language. The data type must be explicitly specified for every variable.

The language features only 3 primitive (scalar) data types:
* `char` - A character in the `windows-1251` encoding.
* `byte` - An integer ranging from 0 to 255. It differs from `char` only in its input/output formatting.
* `bool` - A boolean type (`true`/`false`).

### Declaration and Initialization
Variables can be declared and initialized with a value simultaneously (either individually or via a chained assignment):
```brainfix
# Declaration and initialization
char char_1 = 'a';
byte count = 0, newCount = 30;
bool b = false;

# Chained assignment
byte first = second = third = 1;
```

### Uninitialized Declarations
If a variable is declared without an initial value, memory is allocated for it but is not cleared.
Although the entire Brainfuck tape is filled with zeros when the program starts, memory is reused during runtime. Therefore, an uninitialized variable may contain leftover data from previously deleted variables whose scopes have already ended.

```brainfix
# Variable values are not guaranteed to be zero
byte n;
char ch1, ch2;
bool isFull; # The boolean value might be greater than 1
```

## Literals

Literals are fixed values written directly into the program code. Brainfix supports the following types of literals:

### Boolean Literals
Represented by two keywords for boolean values:
* `true` — Logical true. Corresponds to a value of 1.
* `false` — Logical false. Corresponds to a value of 0.

### Numerical Literals
Written as integers. Numerical literals in code can exceed the maximum value of the `byte` type (255).

> When assigning a numerical literal to a `byte` variable, the final value is calculated **modulo 256**.

```brainfix
byte a = 5;   # In memory: 5
byte b = 256; # In memory: 0 (256 % 256)
byte c = 300; # In memory: 44 (300 % 256)
```

### Character and String Literals
In Brainfix, both individual characters and strings can be enclosed in **either single (`'`) or double (`"`) quotes**. The literal type is automatically determined by the number of characters inside the quotes.

* **Character literal** — Exactly one character inside quotes. Used to initialize the `char` type.
* **String literal** — A sequence of two or more characters inside quotes.

```brainfix
char letter = 'A';    # Character literal
char symbol = "Z";    # Also a character literal (single character)

out "Hello", 'Hello'; # These are string literals
```

### Escape Characters and Newlines
Brainfix provides two ways to write a newline:
* `eol` — A special keyword representing the newline character.
* `\n` — An escape character that can be used inside string literals.

```brainfix
# Both variants produce the same result in this case
out 'Hello, World!', eol;
out 'Hello, World!\n';
```


## Variable Scope

A scope determines the boundaries of a program section where a variable name remains recognized and can be used.

### Block Scope

In Brainfix, variable scope is always restricted by **curly braces `{}`**. Any variable declared inside a code block is local to that specific block. It is created upon entering the block and destroyed upon exiting it.

```brainfix
{
    byte local_x = 10;
    # The local_x variable is accessible here
}
# The local_x variable no longer exists here.
out local_x; # Will trigger a "variable 'local_x' not defined" error
```

### Nested Block Visibility

Inner code blocks have access to all variables that were declared in their outer (enclosing) blocks.

```brainfix
byte global_count = 50;

{
    # The inner block can see the variable from the outer block
    byte current = global_count + 10; 
}
```

### Variable Shadowing

If you declare a variable inside an inner block with the exact same name as a variable in the outer block, the inner variable will **shadow** the outer one.

Inside this inner block, all references to that name will point to the new local variable. The outer variable remains unchanged and becomes accessible again as soon as program execution exits the inner block.

```brainfix
byte value = 10;

{
    byte value = 255; # Shadows the outer 'value' variable
    # Here, value equals 255
}

# The inner block has ended; value equals 10 again here
```

### Special Scope in Loops

An additional rule applies to the `for` loop: variables declared inside the parentheses within the initialization clause `for(here;;) ` are only accessible inside that loop (including its body) and are invisible outside it.

```brainfix
for (byte i = 0; i < 10; i = i + 1) {
    # The variable i is accessible inside this block
    byte result = i * 2;
}
out i; # Error! The variable i is not accessible here
```


## Operators

Operators in Brainfix are used to perform arithmetic computations, logical operations, value comparisons, and assignments.

### Arithmetic Operators

Basic mathematical operations:
* `+` — Addition.
* `-` — Subtraction.
* `*` — Multiplication.
* `/` — Division.
* `%` — Remainder (Modulo).

### Increment and Decrement

These operators are used to increase or decrease a variable's value by one. They are supported in two forms:
* **Prefix (`++a`, `--a`)** — Increments or decrements the variable value first, then returns the result.
* **Postfix (`a++`, `a--`)** — Returns the current variable value first, then increments or decrements it.

### Comparison Operators

Used to compare two values. The result of a comparison is always a boolean value (`true` or `false`).

* `<` — Less than.
* `>` — Greater than.
* `<=` — Less than or equal to.
* `>=` — Greater than or equal to.
* `==` (or `===`) — Equal to.
* `!=` (or `!==`, `<>`) — Not equal to.

```brainfix
byte x = 5;
byte y = 10;
bool result1 = (x < y);   # true
bool result2 = (x == y);  # false
bool result3 = (x <> y);  # true (alias for !=)
```

### Logical Operators

These are applied to `bool` expressions to build complex logical conditions:
* `&&` — Logical AND (conjunction).
* `||` — Logical OR (disjunction).
* `!` — Logical NOT (negation).

### Assignment Operators and Optimization

In addition to basic assignment, the language supports compound assignment operators that combine an arithmetic operation with a value assignment:

* `=` — Basic assignment.
* `+=` — Addition assignment.
* `-=` — Subtraction assignment.
* `*=` — Multiplication assignment.
* `/=` — Division assignment.
* `%=` — Remainder assignment.


## Type Casting

Brainfix features a flexible system for automatic (implicit) type conversion between all three scalar data types (`byte`, `char`, and `bool`).

### Automatic Type Conversion in Assignments

You can freely assign a value of any scalar type to a variable of a different type. The compiler performs the conversion automatically based on the following rules:

* **Number and Character (`byte` <-> `char`)**: The assignment is processed based on the character code in the `windows-1251` encoding.
* **Boolean to Number/Character (`bool` -> `byte`/`char`)**: A `true` value converts to the number `1` (or a character with code 1), while `false` converts to `0`.
* **Number/Character to Boolean (`byte`/`char` -> `bool`)**: Any non-zero value (not equal to `0`) converts to `true`. A zero value becomes `false`.

```brainfix
# byte <-> char
char ch = 'A';    # The character 'A' has a code of 65
byte b = ch;      # b becomes 65
char ch2 = b + 1; # ch2 becomes 'B'

# bool -> byte
bool flag = true;
byte num = flag;  # num becomes 1

# byte -> bool
byte count = 42;
bool isExist = count; # count is not 0, so isExist becomes true
```

### Type Conversion in Expressions

Arithmetic operators (`+`, `-`, `*`, `/`, `%`) **always return a numerical result (the `byte` type)**, regardless of the operand types.

If variables of type `char` or `bool` are used in an expression, they are automatically implicitly cast to their numerical equivalents before the mathematical operation is executed.

```brainfix
char letter1 = 'A'; # code 65
char letter2 = 'B'; # code 66

# Adding two characters results in the number 131 (byte type)
byte result = letter1 + letter2; 

bool active = 5; # converts to 1
byte total = letter1 + active; # 65 + 1 = 66
```

> Brainfix does not have a syntax for explicit type casting (such as `(byte)x`). All conversions occur strictly in an automatic mode based on the context in which the variable is used.


## Input and Output

Brainfix uses only two commands to interact with the user: `in` (for reading data) and `out` (for printing data). Both commands support passing multiple arguments separated by commas.

The formatting of the processed data depends entirely on the type of expression or literal passed to the command.

### Printing Data (`out`)

The `out` command prints values to the output stream. Its behavior is determined by the argument's data type:

* **Literals**: Printed "as is". Numerical literals are not restricted by the limits of the `byte` type. For example, `out 257;` will print exactly `257` on the screen without modulo-256 truncation.
* **`char` Variables**: Printed as a single character.
* **`byte` Variables**: Printed as a numerical value.
  > Printing `byte` values triggers a complex compiler algorithm involving up to three division operations under the hood. Avoid printing numerical expressions inside loops or too frequently.
* **`bool` Variables**: Printed as the digit `1` (for `true`) or `0` (for `false`).

```brainfix
byte num = 65;
char letter = 65;
bool flag = true;

out "Results: ", num, ' ', letter, ' ', flag; 
# Will output: Results: 65 A 1

# You can also print expressions
out 'num * 2 = ', num * 2;
# Will output: num * 2 = 130
```

### Expression Output Specifics

Because the output format in Brainfix depends entirely on the return type of the expression, and explicit type casting is unavailable, the default `out` command might occasionally produce unexpected results.

For example:
1. You want to print a `byte` value as its corresponding ASCII character.
2. You want to shift a character in the encoding table (e.g., `char1 + 1`), but the result of this arithmetic operation automatically becomes a `byte` type and prints as a number.

To work around this limitation, use intermediate variables of the required type.

```brainfix
char char1 = 'A'; # code 65

# PROBLEM: The arithmetic operation returns a byte.
# The code below will print the number "66", not the character "B"
out char1 + 1; 

# SOLUTION: Declare a temporary variable of the desired type
char nextChar = char1 + 1; 
out nextChar; # Will output the character "B"

# Similarly for printing a number as a character:
byte code = 33;
char symbol = code;
out symbol; # Will output the character "!"
```

### Reading Data (`in`)

The `in` command reads data from the standard input stream. Its behavior is determined by the data type of the variable where the value is being stored:

* **Literals (Input Skipping)**: If you pass any literal to the `in` command (e.g., `in 'dummy';`), the program will read exactly one character from the input and simply **discard it**. This is useful for skipping unnecessary characters (like separators) or implementing "Press any key to continue" logic.
* **`char` Variables**: The fastest and simplest input method. It reads a single character from the stream and stores its character code in the variable.
* **`byte` Variables**: Reads all characters until the first space or `Enter` keystroke (inclusive). The resulting string is then converted into a number.
  The entered number can exceed 255. In this case, the value is stored modulo 256.
  If the user enters letters instead of digits, the variable will end up with unpredictable "garbage" data. This behavior can be intentionally leveraged to generate pseudo-random seeds.
* **`bool` Variables**: Reads a single character. If the user enters `0` (character code 48), the variable becomes `false`. In all other cases, it evaluates to `true`.

```brainfix
char ch;
byte age;
bool agreement;

# Waiting for input
in ch, ' ', age, agreement;
# Possible input formats:
# A 25 1
# A
# 25
# 1
# A*25 1

# Skipping a character
out 'Press any key...';
in 'dummy';

# If you need to generate a pseudo-random number
byte seed;
out 'Type any word';
in seed; # An unpredictable pseudo-random number will be stored in seed
```


## Control Structures

Control structures allow you to alter the default linear execution flow of a program based on specific conditions or to repeat sections of code multiple times (loops).

### The `if` / `else` Conditional Statement

Used to execute code conditionally. The statement accepts a boolean expression (or variable) of the `bool` type. The `else` block is optional.

If a block contains only a single statement, the curly braces `{}` can be omitted. If there are multiple statements, the use of curly braces is mandatory.

```brainfix
byte score = 85;

# Usage without curly braces (for a single statement)
if (score >= 50)
    out "Passed!";
else
    out "Failed!";

# Usage with curly braces (for multiple statements)
if (score == 100)
{
    byte bonus = 10;
    out "Perfect score! Bonus: ", bonus;
}
```

### The `while` Loop

A pre-condition loop that executes a block of code as long as the condition inside the parentheses evaluates to `true`. Because the condition is checked **before** the loop body runs, the code inside may not execute at all.

Just like with the `if` statement, curly braces can be omitted if the loop body consists of only a single statement.

```brainfix
byte counter = 5;

while (counter)
{
    out counter, ' ';
    counter--; # Make sure to modify the variable to avoid an infinite loop
}
# Will output: 5 4 3 2 1 
```

### The `do` / `while` Loop

A post-condition loop. The primary difference from a standard `while` loop is that the condition is evaluated **after** the loop body executes. This guarantees that the code inside the loop will run **at least once**, regardless of whether the condition is true or false.

> Note that a semicolon `;` is strictly required right after the closing parenthesis of the condition in a `do / while` loop.

```brainfix
byte x = 10;

do
{
    out ++x;
}
while (x < 5); 
# Will output: 11 (the code executed once, even though 10 is not less than 5)
```

### The `for` Loop

Used to implement counter-based loops. Inside the parentheses, three distinct sections are specified, separated by semicolons:
1. **Initialization** — Declaring and/or setting the initial value of the counter.
2. **Condition** — The loop continues running as long as this expression evaluates to true.
3. **Iteration** — Modifying the counter value after each loop cycle.

Curly braces are also optional if the loop body contains only a single statement.

```brainfix
for (char i = 'A'; i <= 'Z'; i++)
{
    out i, ' ';
}
# Will output:
# A B C D E F G H I J K L M N O P Q R S T U V W X Y Z
```

### Critical Specifics: Absence of `break` and `continue`

Brainfix **completely lacks** the traditional loop control statements `break` (to terminate a loop) and `continue` (to skip to the next iteration).

This fundamental limitation stems directly from the target platform's architecture: the language compiles strictly into low-level **Brainfuck** code, which physically has no instructions for unconditional jumps (the equivalents of `goto`). Every loop in Brainfuck must run completely until its core condition naturally evaluates to `false`.

Because `break` and `continue` are omitted, the language enforces a strict rule: **defining a loop condition without using variables is forbidden**.

```brainfix
while (true) { ... }              # Compilation error: infinite loop detected!
for (byte i = 0; 10; i++) { ... } # Compilation error: infinite loop detected!
```

If you need to terminate a loop prematurely or skip an iteration, you must explicitly modify the condition variable inside the loop body or use `if` conditional statements.

```brainfix
# Working around the absence of 'break' by modifying the condition variable
bool keepRunning = true;
byte wordLength = 0;
char input;

while (keepRunning)
{
    in input;
    if (input == ' ' || input == '\n')
    {
        keepRunning = false; # Instead of break, we update the loop condition itself
    }
    else
    {
        wordLength++;
    }
}
out 'Entered word length: ', wordLength;
```

## Arrays

Arrays in Brainfix allow you to group multiple elements of the same type under a single name. The language supports arrays for all scalar data types (`byte`, `char`, `bool`) and allows the creation of multi-dimensional structures.

### Declaration and Dimensions

Arrays are declared using square braces `[]`, which specify the size of each dimension. Array sizes are fixed at compile time.

```brainfix
byte numbers[255];     # A one-dimensional array of 255 bytes
byte matrix[3][3][3];  # A three-dimensional array (cube) of size 3x3x3
```

### Fixed Size Requirement

Array sizes must be strictly defined at compile time. You are allowed to use **numerical literals only** to specify the length of an array. Setting the size using variables is forbidden, because the compiler must know the exact amount of memory to allocate on the tape in advance.

```brainfix
# Correct: size is defined by a numerical literal
byte static_arr[50]; 

byte size = 10;
# Compilation error: array size must be a constant
byte dynamic_arr[size]; 
```

### Array Initialization

You can initialize an array immediately upon declaration by listing values inside square braces separated by commas, or by specifying a single value for all cells.
```brainfix
# Initialization with numerical literals
byte arr[3] = [ 2, 4, 8 ]; 
# Initialization with a single value. Fills all cells with the character 'A'
char ch[3] = 'A';
```

> You are allowed to use **constant values (literals) only** during initialization. Using variables to set the initial values of array elements is forbidden by the compiler.

```brainfix
byte a = 2, b = 4, c = 8;
# Compilation error: only constant values allowed!
byte arr2[3] = [a, b, c]; 
```

### Architectural Specifics: Why Variables Are Forbidden in Initialization

The restriction against using variables in initialization lists (the `only constant values allowed` error) was introduced strictly for **the sake of optimizing the speed and size of the generated code**.

Since Brainfix compiles directly into Brainfuck, deploying constant values from literals is performed by the compiler in a single, fast, sequential pass over the memory cells. Conversely, copying values from other variables on the Brainfuck tape is an extremely resource-intensive task that requires moving data across multiple cells.

### Initial Values of Array Elements

Memory behavior during array declaration follows the same rules as individual variables. The content of the cells depends on the declaration method:

* **Partial Initialization:** If the declared size of the array exceeds the number of provided literals, all remaining elements are automatically **filled with zeros**.
* **Uninitialized:** If an array is declared without assigning values, the compiler allocates memory for it on the tape but does not clear it. While the entire Brainfuck tape is filled with zeros at program startup, memory is reused during runtime. Therefore, an uninitialized array may contain **leftover data** from previously deleted arrays whose scopes have already ended.

```brainfix
# Partial initialization:
# Elements arr[3] and arr[4] are guaranteed to be 0
byte arr[5] = [ 2, 4, 8 ]; 

# Uninitialized:
# Memory is allocated, but the values of elements numbers[0] ... numbers[255] 
# are undefined and depend on the previous state of the memory tape
byte numbers[256]; 
```

### Automatic Size Calculation

If you initialize an array immediately upon declaration, specifying its dimensions inside the square brackets `[]` is optional. The compiler will automatically count the number of elements and allocate the required amount of memory. This rule also applies to multi-dimensional arrays.

```brainfix
# The compiler will automatically determine the array size as 12 elements
byte fi[] = [ 0, 1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89 ];

# Automatic size calculation for a two-dimensional character array (size 2x3)
char map[][] = [ 
    ['a', 'b', 'c'], 
    ['c', 'd', 'e']
];
```

### Dimension Restrictions

In Brainfix, you can create any number of arrays with any number of dimensions. However, a strict limitation of the target platform is imposed on the structural size: **the size of any single array dimension must not exceed 256 elements**.

```brainfix
byte valid[256];          # Correct: maximum allowed size for a single axis
byte invalid[257];        # Compilation error: dimension exceeds 256!
byte multi[256][256][5];  # Correct: each axis remains within the limit
```

### Element Access and Indexing

To read and write array element values, use the standard square bracket operator `[]`. Array indexing is zero-based.

```brainfix
byte cells[5];

cells[2] = 42;       # Writing a value to the third element of the array
byte val = cells[2]; # Reading a value from the array
```

### Assignment Operator for Arrays

In Brainfix, the assignment operator `=` allows you to perform batch operations on arrays:

* **Bulk Filling with Literals:** You can overwrite array elements with a new list of constant values. The filling rules remain the same: the dimensions must match, and any omitted elements are automatically filled with zeros.
* **Filling with a Single Value:** If you assign a single scalar literal or variable to an array, **all of its cells are filled with that value**.

This is the fastest way to reset or initialize an array during program runtime.

```brainfix
byte data[5] = 10; # Initializing all cells with the value 10

# Overwriting the array with a list of constants
data = [ 2, 4, 8 ]; # data[3] and data[4] automatically become 0

# Filling all cells with a single value
data = 255;         # All array elements become 255
```

> Batch assignment operations work similarly to constant initialization. They execute **significantly faster** than manually iterating through elements via a loop or sequential index-by-index access. The compiler generates the most efficient sequential pass possible across the tape.

> These operators are subject to the same strict limitation of the target platform: **you cannot use variables when batch-assigning a list via square brackets `[...]`**; only constant literals are allowed.


### The `sizeof` Operator

The `sizeof` operator returns the physical size of an array (the total number of its elements) as determined at compile time. It returns a fixed value rather than counting "empty" or zero-filled cells at runtime.

When working with multi-dimensional arrays, `sizeof` returns the length of the **specific specified dimension**, not the total cumulative number of all cells. This allows you to easily structure nested loops to traverse multi-dimensional data structures of any nesting level.

```brainfix
byte data[10][20];

# sizeof data will return 10 (the size of the first axis)
# sizeof data[i] will return 20 (the size of the second axis)
for (byte i = 0; i < sizeof data; i++) {
    for (byte j = 0; j < sizeof data[i]; j++) {
        data[i][j] = i + j;
    }
}
```

### Performance Specifics and Array Optimization

Accessing an array element by its index is one of the most **resource-intensive and slow operations** in Brainfix. Due to the linear memory structure of the target platform (the Brainfuck tape), the compiler must generate pointer-shifting loops to reach the desired cell. The larger the array size, the longer it takes to read or write data.

However, the compiler features clever optimization logic that significantly speeds up operations with multi-dimensional arrays.

The compiler can "jump" across large blocks of cells instantly if it can compute a constant offset multiplier ahead of time (at compile time). For this reason, a multi-dimensional array made of several smaller dimensions executes faster than a flat, one-dimensional array of the same total capacity.

```brainfix
char arr1[256];    # 256 cells in total
char arr2[16][16]; # 256 cells in total
byte i = 15, j = 15, m = 255;

# EXECUTES ALMOST INSTANTLY:
# The compiler calculates the index beforehand and shifts the pointer by 255 cells 
out arr1[255];
out arr2[15][15]; # 15 * 16 + 15 = 255

# EXECUTES SLOWER:
# The compiler shifts the pointer by jumping over blocks of 16 values at a time
out arr2[i][j]; 

# EXECUTES MUCH SLOWER:
# The program has to manually step through the tape cell by cell 255 times
out arr1[m]; 
```

> If you need to allocate a large amount of memory (e.g., 256 cells), it is always more efficient to design an array with multiple smaller dimensions (e.g., `[16][16]`) rather than using a single, large, flat structure.

### Out-of-Bounds Array Access

To maintain maximum execution speed and keep the final generated code compact, Brainfix **does not perform runtime array bounds checking**.

If you attempt to access an element using an index that exceeds the declared array size, the compiler will not throw an error, and the program will not crash. Instead, the pointer on the Brainfuck memory tape will literally shift by the specified number of steps, and the read or write operation will execute in an unintended memory location.

Depending on exactly where the pointer shifts, you will encounter one of two outcomes:
* The value will overwrite a memory cell allocated for **another array**, unpredictably corrupting its data.
* The value will be written into an **unallocated (free) cell** on the memory tape.

```brainfix
char arr[10];
byte i = 11;

# No runtime error will occur!
# The character 'A' will be written to the 12th cell relative to the start of 'arr'.
# This will corrupt the data of variables located on the tape right after this array.
arr[i] = 'A'; 
```

> The entire responsibility for remaining within array bounds lies solely with the programmer. To prevent elusive bugs caused by adjacent data corruption, always restrict index variables in loop conditions or `if` statements using checks against `sizeof`.

## Strings

Brainfix does not feature a dedicated string data type. Strings are implemented simply as **standard arrays of `char` elements**. They are subject to all the same rules, restrictions, and optimization specifics as standard arrays.

### Declaration and Initialization

You can declare and immediately initialize strings using string literals. If you omit the size inside the square brackets `[]`, the compiler will automatically calculate the string length and allocate exactly as many cells as there are characters in the literal.

Creating multi-dimensional arrays of strings is also supported.

```brainfix
# The compiler will create a char array of 13 elements
char str[] = 'Hello, World!';

# A two-dimensional array of strings (size 3x5, as the longest word is 5 characters)
char words[][] = [ 'cat', 'dog', 'mouse' ];
```

### Assigning String Literals

You can modify the contents of a string after its declaration by assigning new string literals to it:

```brainfix
char text[10] = 'init';

text = 'abc'; # The first 3 elements change to 'a', 'b', 'c'; the rest are filled with zeros
```

> **Critical Quote Specifics:**
> As mentioned in the "Literals" section, a value type is determined by its length. The expression `text = 'a';` contains exactly one character, so the compiler treats it as a **character literal**, not a string literal. According to array rules, assigning a single scalar literal fills the **entire array** with that value.

> If you want to write a single letter `a` to the beginning of the array and zero out the rest, you must pass a string literal of at least two characters (for example, by adding a trailing space) or clear the array beforehand.

```brainfix
char status[5] = 'good';

status = 'a'; 
# Warning! The status array now contains: ['a', 'a', 'a', 'a', 'a']
```

### Absence of a Null Terminator (`\0`)

Unlike the C programming language, strings in Brainfix **are not terminated by a hidden `\0` character (null terminator)**. The array contains strictly meaningful characters only. This architectural decision was made to save precious space on the Brainfuck memory tape. A string's length is always exactly equal to the physical size of its array.

### String Input and Output (`in` / `out`)

The `in` and `out` commands natively support `char` arrays:

* **Output (`out str;`)**: Prints all characters of the array to the output stream from index zero to the end of its physical size.
* **Input (`in str;`)**: Reads characters from the input stream until the user presses Enter (end of line).

```brainfix
# Example: String reversal
char str[256];

in str; # Read a string from the input stream

# Print string characters in reverse order
for (byte i = 255; i > 0; i--)
{
    out str[i];
}
out str[0]; # Print the final character separately due to byte type underflow
```

### Specifics and Risks of String Input

1. **Short Input (Retaining Leftover Data)**: If the string entered by the user is shorter than the size of the array, the read characters will overwrite the beginning of the array, but **the remaining cells will not be cleared**. They will retain their old values.

    ```brainfix
    char str[20] = '-'; # Fill the entire array with hyphens
    
    in str;  # The user types: abc
    out str; # Will output: abc-----------------
    ```

2. **Long Input (Out-of-Bounds Writing)**: If the user inputs a string that exceeds the array size, Brainfix **will not perform automatic truncation**.

   > The `in` command will faithfully continue reading the input stream and writing the "excess" characters into memory cells located past the array's boundaries. This will corrupt the data of adjacent arrays situated next on the memory tape. Always allocate input string sizes with a safety margin.


## Functions and Subroutines

Brainfix **completely lacks functions, procedures, and subroutines**. Any code you write executes strictly in a linear fashion, from top to bottom.

### The Reason Behind Omitting Functions

This fundamental architectural choice is a conscious compromise driven by the severe restrictions of the target platform (the Brainfuck tape):

* **Absence of Addressing and Jumps:** Classic Brainfuck physically provides no equivalents to `goto` or `call/return` operators. You cannot "jump" to another part of the code to execute a function and then return to the original call site.
* **The Code Duplication Dilemma:** The only technical way to implement functions under these constraints would be the complete copying (inlining) of the entire function body into every single location where it gets called. If a function were called 10 times in a program, the compiler would have to duplicate its low-level code 10 times.

Such duplication would cause exponential growth in the final `.bf` file size. Consequently, the decision was made to omit function support in Brainfix entirely.

The entire program logic must be structured within a single, continuous execution flow utilizing arrays, loops, and conditional statements.
