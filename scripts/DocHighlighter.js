import { StringStream } from '@codemirror/language';

export class DocHighlighter {
	constructor() {
		this.languages = {};
		this.cssRules = [];
	}

	registerLanguage(langName, languageObject, highlightStyle) {
		const tokenClasses = {};
		const tokenNameToClass = {};

		highlightStyle.specs.forEach((spec, index) => {
			const className = `cm-doc-${langName}-${index}`;
			tokenClasses[spec.tag] = className;

			let styles = [];
			if (spec.color) styles.push(`color: ${spec.color};`);
			if (spec.fontStyle) styles.push(`font-style: ${spec.fontStyle};`);
			if (spec.fontWeight) styles.push(`font-weight: ${spec.fontWeight};`);

			this.cssRules.push(`.${className} { ${styles.join(' ')} }`);
		});

		languageObject.streamParser.tokenTable?.mappings?.forEach((tag, name) => {
			if (tokenClasses[tag]) {
				tokenNameToClass[name] = tokenClasses[tag];
			}
		});

		const customHighlighter = (tag) => tokenClasses[tag] || null;

		this.languages[langName] = {
			parser: languageObject.streamParser,
			tokenClasses,
			tokenNameToClass,
			customHighlighter
		};
	}

	getCssCode() {
		return this.cssRules.join('\n');
	}

	_escapeHtml(text) {
		return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
	}

	getMarkdownHighlightFunction() {
		return (code, lang) => {
			const langConfig = this.languages[lang];
			if (!langConfig) return '';

			try {
				let htmlResult = "";
				const lines = code.split('\n');
				let state = langConfig.parser.startState();

				lines.forEach((line, index) => {
					if (line === "" && index === lines.length - 1) return;

					const stream = new StringStream(line);

					while (!stream.eol()) {
						const start = stream.pos;
						const tokenType = langConfig.parser.token(stream, state);
						const text = line.slice(start, stream.pos);

						const className = tokenType ? (langConfig.tokenNameToClass[tokenType] || langConfig.tokenClasses[tokenType]) : null;

						if (className) {
							htmlResult += `<span class="${className}">${this._escapeHtml(text)}</span>`;
						} else {
							htmlResult += this._escapeHtml(text);
						}
					}
					htmlResult += '\n';
				});

				return `<pre class="cm-editor"><code class="cm-scroller">${htmlResult}</code></pre>`;

			} catch (err) {
				console.error(`Ошибка подсветки для языка [${lang}]:`, err);
				return '';
			}
		};
	}
}