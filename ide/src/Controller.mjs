import {Editor} from "./Editor.mjs";
import {Translator} from "./lib/Translator.mjs";
import {MetaParser} from "./lib/MetaParser.js";

export class Controller {
	constructor(editor, profiler, console, input) {
		this._editor = editor;
		this._profiler = profiler;
		this._console = console;
		this._input = input;
		this._translator = new Translator(
			(text) => this._console.echo(text)
		);
		this._stopped = true;
		this._running = false;
	}

	onRun = () => {
		this._compile() && this._run();
	}

	onStop = () => {
		if (this._stopped) { return; }
		this._dropHeaders();
		this._stopped = true;
		this._console.stop();
		this._console.setStatus('stopped');
		this._editor.highlightPosition(null);
	}

	onStep = () => {
		if (this._running) { return; }
		if (this._stopped) {
			if (!this._compile()) { return; }
			this._renderState();
			return;
		}

		this._run(true, { oneStep: true });
	}

	onStepLine = () => {
		if (this._running) { return; }
		if (this._stopped) {
			if (!this._compile()) { return; }
			this._renderState();
			return;
		}

		this._run(true, { lineStep: true });
	}

	onStepOut = () => {
		if (this._running || this._stopped) { return; }

		this._run(true, { stepOut: true });
	}

	_compile() {
		this._console.clear();
		try {
			const text = this._editor.getCode();
			this._applyHeaders(text);

			this._translator.compile(text);
			this._translator.pushInput(this._input.get());
			this._profiler.reset(text);
		}
		catch (e) {
			this._console.showError(e.message);
			this._editor.highlightPosition(null);
			console.warn(e);
			return false;
		}
		this._stopped = false;
		return true;
	}

	_run = (debug = false, runParams = {}) => {
		if (this._stopped) {
			this._running = false;
			return;
		}
		this._running = true;
		try {
			this._translator.run(debug, runParams);

			this._running = false;

			if (this._translator.getCurrentPosition() === null) {
				this._stopped = true;
				this._console.setStatus('finished');
			} else {
				this._console.setStatus('waiting');
			}
		}
		catch (e) {
			if (e.message === 'timeout') {
				this._console.setStatus('running');
				setTimeout(this._run, debug, runParams);
			} else if (e.message === 'need input') {
				this._console.readInput().then((input) => {
					this._translator.pushInput(input);
					this._run(debug, runParams);
				})
				this._console.captureFocus();
			} else {
				this._console.showError(e.message);
				console.warn(e);
				this._stopped = true;
				this._running = false;
			}
		}
		this._renderState();
	}

	_renderState() {
		const position = this._translator.getCurrentPosition();
		this._editor.highlightPosition(position);
		this._profiler.render(this._translator.getStorage(), this._translator.getPointer(), position);
		this._console.setCommandsCount(this._translator.commandsCount());
	}

	_applyHeaders(code) {
		const headers = MetaParser.parseHeaders(code);

		const bufferedInput = headers['buffered_input'] ?? 'on';
		const stepsPerFrame = headers['steps_per_frame'] ?? '';
		const consoleColor = headers['console_color'] ?? '';

		this._console.setColor(consoleColor);
		this._console.setUseInputBuffer(MetaParser.parseBool(bufferedInput, true));
		this._translator.setStepsPerFrame(MetaParser.parseInt(stepsPerFrame, null));
	}

	_dropHeaders() {
		this._console.setColor();
		this._console.setUseInputBuffer();
		this._translator.setStepsPerFrame();
	}
}