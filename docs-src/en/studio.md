# Brainfix Studio Guide

> This documentation is automatically translated by an AI language model. If you notice any inaccuracies or prefer the original text, you can switch to the **[Russian Version](../)**.

**Brainfix Studio** is a fully featured, web-based development environment (Web IDE) designed for writing, compiling, debugging, and running programs in both Brainfix and Brainfuck. It provides all the necessary tools for comfortable programming directly in your browser and is accessible via the following link:

[Launch Brainfix Studio](https://gordey1999.github.io/brainfix/)


## Interface Overview

The Brainfix Studio interface is designed as a modular workspace divided into several functional areas. **All blocks can be resized** simply by dragging their borders with your mouse.

![Studio Interface Layout](../assets/img/studio-interface.png)

The layout highlights the following key sections:

* **Control Panel (Button Block):** Located at the top of the screen. The set of available tools on this panel **changes dynamically based on the selected language**.
* **Code Editor:** The primary workspace on the left side of the screen. It features tab support, separate syntax highlighting, and allows you to quickly switch between high-level source files and low-level machine code.
* **Terminal:** A console window in the top-right corner that displays compilation details for `.bfx` files, as well as the output of the executed `.bf` program. This is also where you type data for the program's input stream.
* **Memory Dump (Tape Visualization):** A window in the bottom-right corner that visualizes the current state of memory cells within the Brainfuck virtual machine. During execution or step-by-step debugging, you can watch live as the pointer shifts and values are written to specific cells.
* **Input File Block:** A dedicated block where you can pre-fill input data for the `.bf` program, preventing the need to re-type it manually on every single run.


## Code Editor

The centerpiece of Brainfix Studio is a tabbed code editor that allows you to work with multiple files simultaneously and even handle different programming languages.

### Two Environments in a Single Interface

The editor is engineered to let you write code in both high-level Brainfix and low-level Brainfuck. Tabs are visually color-coded so you always know which language you are actively working with:
* **White Tabs** — Source code files in the **Brainfix** language.
* **Yellow Tabs** — Raw code files in the **Brainfuck** language.

To create a new file, simply click the **`+`** (plus) button of the corresponding color.

### Smart Syntax Highlighting

The Brainfix Studio editor features smart, outlined syntax highlighting. It automatically adapts to the active tab type (white or yellow) and handles the following tasks:

* **For Brainfix**: Colors highlight keywords (the `in` and `out` commands), data types (`byte`, `char`, `bool`), control structures (`if`, `while`, `for`), variables, comments, and project headers.
* **For Brainfuck**: The editor applies distinct color schemes to differentiate between various comment types and specific comment contents.


## Control Panel (Brainfuck Mode)

When you switch to a **yellow tab** containing `.bf` code, the upper control panel transforms into a full-featured console for managing the virtual machine and the debugger.

![Control Panel for BF](../assets/img/studio-buttons-bf.png)

### Basic Controls
* **RUN** — Launches the program in standard execution mode. The code runs at maximum speed until it encounters the first input request instruction `,` or reaches the end of the file.
* **STOP** — Instantly terminates the execution of a running or actively debugged program and resets the state of the virtual machine.

### Step-by-Step Debugging Mode
To initiate program debugging, simply click the **STEP** or **LINE** button. During a debugging session, a live indicator activates within the editor: the environment visually highlights both the active code line and the specific instruction character where the interpreter is currently paused.

Three distinct commands are available to control execution stepping:
* **STEP** — Executes exactly one low-level Brainfuck instruction (e.g., a single `+` or `>`) and pauses execution, awaiting the next action.
* **LINE** — Executes the entire current line of code and advances the instruction pointer to the beginning of the next line.
* **OUT** — Executes the code at high speed until the program completely steps out of the current `[]` loop where the pointer is currently located.

### Test Automation (INPUT)
* **INPUT** — Toggles the input file mode.

When this button is active, an additional "Input File" panel opens on the right side of the interface. You can pre-fill this block with any text data that your program is expected to process via input commands. This eliminates the need to manually re-type the exact same strings into the console during every test run.

> If the pre-filled "input file" data runs out while the program executes another `,` (input request) instruction, the virtual machine will automatically fall back to waiting for real-time user input via the Terminal.


## Control Panel (Brainfix Mode)

When you switch to a **white tab** containing `.bfx` source code, the toolbar reconfigures completely to focus on compilation, code generation, and metadata management tasks.

![Control Panel for BFX](../assets/img/studio-buttons-bfx.png)

### Compilation Modes
* **BUILD** — Standard compilation of source code into readable Brainfuck.
  The assembly result always opens in a **separate new yellow tab**. The generated `.bf` file includes thorough text comments detailing exactly which high-level source code snippets produced specific low-level instruction blocks.

  > Along with the code, the compiler bakes special service markup into the comments. Thanks to this, the "Memory Dump" window in Brainfix Studio automatically extracts your variable names from the `.bfx` file and labels the corresponding cells on the tape during debugging.

* **MIN** — Compiles the code into an optimized `.min.bf` format.
  All service comments and whitespaces are stripped entirely from the resulting file (only the program's meta-headers are retained). The entire generated Brainfuck code is packed into a single, continuous, compact string. This mode is ideal for generating a final script ready to be exported to other platforms.

### Modifiers and Automation
* **UGLY** — Toggles the aggressive optimization mode.
  When this button is active, clicking **BUILD** or **MIN** instructs the compiler to employ mathematical compression algorithms: all long sequences of additions or subtractions (e.g., chains of thirty `+` characters) are replaced with short, generated multiplication loops.

  This makes the final `.bf` file **significantly shorter in size**, though the code becomes less human-readable and takes slightly longer to execute on the virtual machine due to multiplication loop overhead.

    ```brainfuck
    # 'Hello, World!' program with UGLY option disabled
    ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++.+++++++++++++++++++++++++++++.+++++++..+++.-------------------------------------------------------------------.------------.+++++++++++++++++++++++++++++++++++++++++++++++++++++++.++++++++++++++++++++++++.+++.------.--------.-------------------------------------------------------------------.[-]
    
    # The same program with UGLY option enabled
    >++++++++[-<+++++++++>]<.>++++[-<+++++++>]<+.+++++++..+++.>++++++[-<----------->]<-.------------.>+++++[-<+++++++++++>]<.>++++[-<++++++>]<.+++.------.--------.>++++++[-<----------->]<-.[-]
    ```

* **INPUT** — Toggles the input file panel.
  Operates identically to the Brainfuck mode. If you pre-fill the input file field on a `.bfx` tab, clicking the compilation buttons will automatically duplicate and transfer those test data fields into the newly opened yellow `.bf` tab. You will not have to re-enter them manually.


## Terminal

The Terminal in Brainfix Studio combines the functionalities of a system console, input/output streams (`in` / `out`), and a performance monitor for the Brainfuck virtual machine.

### Performance Monitor and Statuses

![Program Execution Status](../assets/img/studio-terminal-status.png)

A status line containing crucial information is located at the top of the terminal window:
* **Execution Status (Top-Left Corner):** Displays the current system state in real time. It shows system notifications regarding the `.bfx` file compilation process, successful assembly completions, active `.bf` code execution, or terminations.
* **Instruction Counter (Top-Right Corner):** A dynamic counter that tracks the exact number of low-level Brainfuck instructions executed since the program started. The counter automatically resets to zero upon every new run, allowing you to accurately measure algorithm efficiency and the degree of compiler optimization.

### Input and Output Streams
The main area of the terminal is dedicated to a classic console window:
* **Data Output:** All characters, numbers, and strings sent by the program via the `out` command are instantly printed here.
* **Data Input:** When the program reaches an `in` reading instruction, you can type data directly into the console. Paste operations (**Ctrl + V**) are also fully supported.

### Input Buffering Modes
By default, the terminal operates with **line buffering** enabled. This means that all characters you type are accumulated in the input bar first and are only sent to the program stream after you press **Enter**. Until Enter is pressed, you can freely edit, erase, or append your text.

> If necessary, buffering can be disabled completely (forcing the program to respond to every single keystroke instantly), and the text/background colors of the terminal can be customized. However, these parameters are not configured via interface buttons but are defined directly within the program's source code—this is covered in detail in the **"Meta-headers"** section.

### Compilation Log in the Terminal

When you are on a white tab (`.bfx`) and click the **BUILD** or **MIN** button, the terminal console outputs a detailed technical report regarding the program assembly process. This log allows you to evaluate exactly how the compiler allocated memory on the Brainfuck tape.

![Compilation Log](../assets/img/studio-build-info.png)

#### Log Parameter Breakdown:
* **version** — The current version of the Brainfix language and compiler.
* **registry size computed** — The number of service memory cells that the compiler automatically allocated to perform intermediate calculations, mathematical operations, condition processing, and temporary data copying.
* **stack size computed** — The volume of memory (in cells) allocated to store all single scalar variables (`byte`, `char`, `bool`) declared in the program.
* **arrays stack size computed** — The total volume of memory allocated on the tape to store all arrays and strings.
* **finished! code length** — A notification of successful compilation completion, specifying the exact length of the resulting `.bf` file (the total number of low-level Brainfuck instructions generated).


## Memory Dump and Layout Directives

The **Memory Dump** window is located in the bottom-right corner of the Brainfix Studio interface. It serves as a visual map where the memory cells of the Brainfuck virtual machine are listed sequentially from left to right and top to bottom.

![Memory Dump Window](../assets/img/studio-memory-dump.png)

This section allows you to monitor the memory state of the running program in real time. Each cell displays three parameters:
1. The cell's **index** on the tape (displayed on the left side).
2. The cell's textual value as a **Windows-1251 encoded character**.
3. The cell's numerical value (ranging from 0 to 255).

In addition to the cell values themselves, the dump window visualizes the **active memory pointer**. The specific cell where the Brainfuck interpreter's carriage is currently resting is highlighted with color. As the shift instructions `>` and `<` execute, you can visually track how this pointer moves across the memory tape.

### Update and Animation Specifics

* **Update Frequency**: The memory dump is not rendered on every single step of the pointer; instead, it updates cyclically—by default, every **10 million executed instructions**, or whenever the program pauses to await new data input from the user.
* **Change Indication**: Cells whose numerical values have changed since the previous render cycle are automatically **highlighted in blue**.

### Memory Layout Directives (`@memory`)

To prevent the debugging of low-level Brainfuck code from turning into a guessing game of indices, Brainfix Studio includes built-in support for **memory layout directives**. These are written into the code as special comments following this specific format:

```brainfuck
# @memory ADDRESS:LABEL ADDRESS:LABEL ...
```

For a standard Brainfuck interpreter, these lines remain regular comments and are completely ignored. However, the internal Memory Dump module in Brainfix Studio scans the program code: during execution, the interpreter reads all layout directives located above the current instruction pointer position and replaces the standard numerical cell indices with clear, readable textual aliases (**LABEL**).

```brainfuck
# Layout directive usage example
# Memory:
# @memory 0:dividend
# @memory 1:divisor
# @memory 2:quotient
# @memory 3:remainder
# @memory 4:tmp
# @memory 5:tmp
# @memory 6:tmp
```

### Automatic Layout Mapping During Compilation

When you compile high-level `.bfx` code into the `.bf` format using the **BUILD** button, the compiler automatically generates and inserts all the necessary layout directives. It transforms abstract variables into a well-defined structure on the memory tape:

1. **General-Purpose Registers (`R0`, `R1`, `R2`...)** — Occupy the very first cells of the tape. The compiler uses these to store intermediate results of mathematical calculations and logical checks.
2. **Variable Stack** — Positioned directly after the registers. Cells in this region automatically receive the exact same names you gave to single scalar variables (`byte`, `char`, `bool`) in your `.bfx` source code.
3. **Array Control Service Cells (`adr_s`, `adr_d`)** — Placed right after the variable stack. These two cells are critical for the compiler: they are used for address calculation and executing pointer "jumps" across data structure elements.
4. **Array Block** — The final part of the layout. For each element of any declared array, the compiler allocates exactly **two physical cells**:
    * *Index Cell (`i0`, `i1`, `i2`...)* — A service marker required by the compiler's internal algorithms to traverse the structure correctly.
    * *Value Cell* — Stores the actual data of the element. It is labeled with the array name from the `.bfx` source along with its respective index, for example: `arr[0]`, `arr[1]`, `arr[2]`.

Thanks to this automatic layout mapping, when you start a step-by-step debugging session of your compiled code, you will see a highly intuitive map in the dump window instead of a faceless array of numbers: you will know exactly where your variables reside, how the elements of a `matrix` array change, and which registers are actively processing calculations at any given second.


## Project Management

Brainfix Studio features a built-in project management system that allows you to save your progress, export files to your local machine, and share your code.

### Data Storage and Security

All projects, file history, and interface configurations are saved **strictly within your browser's local storage (IndexedDB)**. The Studio handles your files locally: your project source code remains entirely on your machine and is never sent or saved to external servers.

> **Compilation Specifics:** The development environment operates in a hybrid mode. While data storage and code execution happen entirely locally on your computer, the process of translating high-level `.bfx` code into low-level `.bf` instructions is handled on a remote server.

### The Save Menu

To commit your progress, use the save menu accessible via the **SAVE** button on the upper control panel.

![Save Menu](../assets/img/studio-save.png)

The project management workflow inside this menu mirrors the slot system commonly found in video games. Within this menu, you can:
* Create a new slot for the current project.
* Overwrite an existing slot.
* Rename a project within the slot list.
* Delete an unwanted slot from the local database.

To back up and transfer your projects across devices, files with the `.bfp` (Brainfix Project) extension are used:
* **export** — Allows you to dump the project and manually choose a specific target directory and filename on your computer to save it.
* **download** — Triggers a quick download of the `.bfp` project file directly into your browser's default system "Downloads" folder.

### Autosave State and Critical Limitations

The IDE periodically **autosaves the entire state of your workspace**. The cache stores not only the program texts but also the complete file modification history (**Ctrl + Z** / **Ctrl + Shift + Z**), along with the exact current dimensions and proportions of all resizable windows on the screen. If you accidentally close your browser tab or refresh the page, the Studio will restore the interface exactly as it was right before you left.

> Due to this built-in autosave mechanism, **it is highly discouraged to open Brainfix Studio in multiple browser tabs or windows simultaneously**. Doing so may result in local storage synchronization conflicts. Always stick to a single active tab.


## The Load Menu and Built-in Examples

![Load Menu](../assets/img/studio-load.png)

To open previously saved projects or launch demonstration programs, use the load menu accessible by clicking the **LOAD** button on the upper control panel.

The interface of this menu is split into several blocks and provides the following features:

### Managing Local Saves
The menu displays a comprehensive list of your personal slots previously created via the save menu. Right from this screen, you can:
* **Load** a selected project into the editor to resume working on it.
* **Rename** a slot directly in the list.
* **Delete** an old or unwanted local save.

### Pre-installed Demo Programs
A dedicated section of the load menu features pre-installed demonstration programs (including complex projects like the interactive "Minesweeper"). Every demo program comes complete as a compiled low-level `.bf` file alongside its original high-level `.bfx` source code.

You can freely load any demonstration project, analyze its architecture, modify the code to fit your needs, and save your modifications as a new custom local project.

### Importing External Projects
The **import** button is located at the very bottom of the load menu. It is designed to open `.bfp` project files stored on your computer. When you select a file through this menu, it is imported into the IDE and deployed across the editor tabs exactly as if you had loaded it from a regular local slot.


## Meta-headers (Program Configuration)

**Meta-headers** are special service comments used by the compiler and the Brainfix Studio environment to configure assembly options, interface settings, and code execution parameters.

```brainfuck
# Meta-headers example for Minesweeper
# @TITLE: MINESWEEPER!
# @BUFFERED_INPUT: FALSE
# @STEPS_PER_FRAME: 50M

>>>>>>>[-]<  <<<+++[->>>>++++++  <<<<]>>>>>[-]+++++  +<<<<<++++
...
```

The primary advantage of this approach is that the configuration for both the IDE and the compiler changes individually for each specific file, rather than modifying the environment as a whole.

### Layout Rules and Syntax

* **Placement**: Meta-headers must be located **strictly at the very beginning of the file**, preceding the first executable instruction of the program (this applies to both `.bf` and `.bfx` files). If even a single language command appears before a meta-header, it will be ignored and treated as a regular comment.
* **Case Sensitivity**: Meta-header keys are case-insensitive (`@TITLE` and `@title` operate identically).
* **Format**: Three syntax variants are permitted to separate the key from its value — via a space, a colon, or an equals sign.

```brainfix
# CORRECT: headers are declared before the code
# @title: My Program
# @console_color = #00ff00
byte x = 10;
out x;

byte x = 10;
# INCORRECT: headers are declared after an executable instruction
# @title: My Program (This will be ignored!)
out x;
```

### Universal Headers (for both `.bf` and `.bfx`)

* **`# @title`**  
  Defines the program name displayed as the label of the active editor tab within the Studio.

    ```brainfix
    # @title: Hello, World!
    out 'Hello, World!';
    ```


### Runtime Headers (for `.bf`)

These headers control the behavior of the virtual machine while the script is running. It is important to remember that all settings apply only during program execution and automatically reset to their default values as soon as the program terminates.

* **`# @console_color`**  
  Sets the text color within the Terminal window. The value accepts any valid CSS color format: string name, HEX code, or RGB.  
  *Examples:* `# @console_color red` or `# @console_color #ff0000`

* **`# @buffered_input`**  
  Enables or disables line buffering for inputs in the Terminal. When buffering is enabled, data typed by the user is sent to the program stream only after pressing the Enter key. When buffering is disabled, the program instantly reads every character the moment a key is pressed.
    * *Enabled (default):* `true`, `yes`, `on`, `y`, `1`
    * *Disabled:* `false`, `no`, `off`, `n`, `0`
    * *Example:* `# @buffered_input false`

* **`# @steps_per_frame`**  
  Specifies the number of low-level Brainfuck instructions that the virtual machine must execute before each user interface update (render). Text rendering in the Terminal, Memory Dump animations, and current-line highlighting in the editor occur cyclically after passing this specified step limit (or when the program pauses awaiting user input).
    * By default, rendering occurs every **10M** (10 million) instructions.
    * Allowed suffixes are **K** (thousands), **M** (millions), or raw integers.
    * *Use Case:* Small values (e.g., `100`) are ideal for visually observing slowed step-by-step execution of an algorithm. Large values (e.g., `100M`) help eliminate interface freezes and lags if the program cyclically redraws a large amount of content upon every user input.
    * *Example:* `# @steps_per_frame 100K`

> Headers meant for `.bf` can and should be defined within `.bfx` code headers. During assembly, the compiler will automatically copy them to the beginning of the generated file, saving you from having to configure them manually after every single compilation.



### Compiler Headers (for `.bfx` only)

These directives directly impact the translator's logic during the assembly of high-level source code.

* **`# @comment_level`**  
  Configures the documentation depth within the generated `.bf` code. The compiler is capable of generating three distinct types of comments:
    1. *Memory Mapping (`# @memory ...`)* — Service data used to label cells within the dump window.
    2. *Source Code Reference (`### byte a = 10;`)* — Blue-colored comments indicating exactly which line of `.bfx` code produced the subsequent block of Brainfuck instructions.
    3. *Operation Explanations (`# add 72 to R0`)* — Green-colored comments describing low-level action sequences executed by the compiler.

  Available configuration values include:
    * `full` (default) — Retains all generated comments.
    * `source` — Retains memory mapping directives and blue source code reference comments only.
    * `memory` — Retains memory mapping directives exclusively.
    * `none` — Assembles the file without any comments. The output is clean, structured Brainfuck code containing only meta-headers.
    * *Example:* `# @comment_level source`

* **`# @version`**  
  Specifies the target language and compiler version.
    * `1.0` — The initial compiler and syntax release.
    * `1.1` (default) — The latest version. Features global architecture enhancements that drastically optimize and accelerate multi-dimensional array operations.
    * *Example:* `# @version 1.0`
