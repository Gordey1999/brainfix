import {basicSetup} from "codemirror"
import {EditorView, keymap, Decoration} from "@codemirror/view"
import {StreamLanguage, HighlightStyle, syntaxHighlighting, bracketMatching, indentUnit} from "@codemirror/language"
import {tags} from "@lezer/highlight"
import { indentWithTab, historyField } from "@codemirror/commands"
import { StateField, StateEffect, EditorState } from "@codemirror/state"
import {bfExt} from "./lib/bf-lang.mjs";
import {bfxExt} from "./lib/bfx-lang.mjs";

const setActivePosition = StateEffect.define()
const activeLineDeco = Decoration.line({
	class: "cm-active-debug-line"
})
const activeCharDeco = Decoration.mark({
	class: "cm-active-debug-char"
})

const setErrorPosition = StateEffect.define()
const errorDeco = Decoration.mark({
	class: "cm-compile-error"
})

const activeLineField = StateField.define({
	create() {
		return Decoration.none
	},

	update(deco, tr) {
		deco = deco.map(tr.changes)

		for (let e of tr.effects) {
			if (e.is(setActivePosition)) {
				if (e.value === null) {
					return Decoration.none
				}

				try {
					const line = tr.state.doc.line(e.value[0] + 1);
					const char = line.from + e.value[1];

					deco = Decoration.set([
						activeLineDeco.range(line.from),
						activeCharDeco.range(char, char + 1)
					])
				}
				catch (e) {
					return Decoration.none
				}
			}
		}
		return deco
	},

	provide: f => EditorView.decorations.from(f)
})

const compileErrorField = StateField.define({
	create() {
		return Decoration.none
	},

	update(deco, tr) {
		deco = deco.map(tr.changes)

		for (let e of tr.effects) {
			if (e.is(setErrorPosition)) {
				if (e.value[0] === null && e.value[1] === null) {
					return Decoration.none
				}

				try {
					const charFrom = e.value[0]
					const length = e.value[1]

					if (length === 0) {
						return Decoration.none
					}

					deco = Decoration.set([
						errorDeco.range(charFrom, charFrom + length)
					])
				}
				catch (e) {
					return Decoration.none
				}
			}
		}
		return deco
	},

	provide: f => EditorView.decorations.from(f)
})

const scrollExt = EditorView.scrollMargins.of(() => ({ top: 50, bottom: 50 }));

export class Editor {
	_states = {};
	_currentState = null;
	_editor = null;
	_onChangeCallback = [];

	constructor(parent, code = '') {
		this._defineBf();
		this._defineBfx();

		this._defaultExt = [
			basicSetup,
			keymap.of(indentWithTab),
			bracketMatching(),
			activeLineField,
			compileErrorField,
			scrollExt,

			EditorView.updateListener.of((update) => {
				if (update.docChanged) {
					for (let callback of this._onChangeCallback) {
						callback();
					}
				}
			})
		];

		this._editor = new EditorView({
			parent: parent,
		})
	}

	onChange(callback) {
		this._onChangeCallback.push(callback);
	}

	addState(name, code, language) {
		const languageExt = language === 'bf' ? this._bfExt : this._bbExt;

		this._states[name] = {
			state: EditorState.create({
				doc: code,
				extensions: [...this._defaultExt, ...languageExt],
				selection: { anchor: code.length }
			}),
			scrollTop: 0,
			scrollLeft: 0,
		}
	}

	switchState(name) {
		this._updateState();

		if (!this._states[name]) {
			throw new Error(`State ${name} not found`);
		}
		const state = this._states[name];
		this._editor.setState(state.state);
		window.requestAnimationFrame(() => {
			this._editor.scrollDOM.scrollTop = state.scrollTop;
			this._editor.scrollDOM.scrollLeft = state.scrollLeft;
		});
		this._editor.focus();

		this._currentState = name;
	}

	getState(name) {
		if (this._currentState === name) {
			this._updateState();
		}

		return this._states[name];
	}

	getStateCode(name) {
		return this.getState(name).state.doc.toString();
	}

	clearStates() {
		this._states = {};
	}

	removeState(name) {
		this._states[name] = null;
	}

	_defineBf() {
		this._bfExt = bfExt;
	}

	_defineBfx() {
		this._bbExt = bfxExt;
	}

	highlightPosition(position) {
		this._editor.dispatch({effects: setActivePosition.of(position)});

		if (position !== null) {
			const line = this._editor.state.doc.line(position[0]);
			this._editor.dispatch({
				effects: EditorView.scrollIntoView(
					line.from,
					{
						y: 'nearest',
						yMargin: 200,
					}
				)
			});
		}
	}

	highlightError(from = null, length = null) {
		this._editor.dispatch({effects: setErrorPosition.of([from, length])});
	}

	getCode() {
		return this._editor.state.doc.toString();
	}


	getSerializableState(name) {
		const state = this.getState(name);

		let serializedState = null;
		try {
			serializedState = state.state.toJSON({ history: historyField });
		} catch (e) {
			console.warn("Не удалось сериализовать стейт для " + name, e);
		}

		return {
			scrollTop: state.scrollTop,
			scrollLeft: state.scrollLeft,
			serializedState: serializedState,
		};
	}

	setSerializableState(name, language, code, data) {
		const languageExt = language === 'bf' ? this._bfExt : this._bbExt;

		let finalState = null;

		if (data.serializedState) {
			try {
				finalState = EditorState.fromJSON(
					data.serializedState,
					{ extensions: [...this._defaultExt, ...languageExt] },
					{ history: historyField }
				);
			} catch (e) {
				console.error("Ошибка десериализации для " + name, e);
			}
		}

		if (!finalState) {
			finalState = EditorState.create({
				doc: code,
				extensions: [...this._defaultExt, ...languageExt]
			});
		}

		this._states[name] = {
			state: finalState,
			scrollTop: data.scrollTop || 0,
			scrollLeft: data.scrollLeft || 0
		};
	}

	_updateState() {
		if (this._currentState !== null) {
			this._states[this._currentState] = {
				state: this._editor.state,
				scrollTop: this._editor.scrollDOM.scrollTop,
				scrollLeft: this._editor.scrollDOM.scrollLeft
			};
		}
	}
}