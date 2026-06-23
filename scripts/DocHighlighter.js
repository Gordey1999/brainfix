import { StringStream } from '@codemirror/language';

export class DocHighlighter {
	constructor() {
		this.languages = {}; // Хранилище для языков
		this.cssRules = [];  // Общий список CSS правил
	}

	/**
	 * Регистрирует новый язык для подсветки
	 * @param {string} langName - Имя, которое пишется в markdown (например, 'brainfuck')
	 * @param {object} languageObject - Объект языка (bfLanguage или bfxLanguage)
	 * @param {object} highlightStyle - Стили темы (bfHighlight или bfxHighlight)
	 */
	registerLanguage(langName, languageObject, highlightStyle) {
		const tokenClasses = {};
		const tokenNameToClass = {};

		// 1. Превращаем HighlightStyle в CSS-правила
		highlightStyle.specs.forEach((spec, index) => {
			const className = `cm-doc-${langName}-${index}`;
			tokenClasses[spec.tag] = className;

			let styles = [];
			if (spec.color) styles.push(`color: ${spec.color};`);
			if (spec.fontStyle) styles.push(`font-style: ${spec.fontStyle};`);
			if (spec.fontWeight) styles.push(`font-weight: ${spec.fontWeight};`);

			this.cssRules.push(`.${className} { ${styles.join(' ')} }`);
		});

		// 2. Маппим строковые имена токенов из таблицы StreamLanguage на наши классы
		languageObject.streamParser.tokenTable?.mappings?.forEach((tag, name) => {
			if (tokenClasses[tag]) {
				tokenNameToClass[name] = tokenClasses[tag];
			}
		});

		// Дополнительно маппим кастомные строки токенов (например, 'modifier')
		// Если в tokenTable нет точного совпадения, ищем по тегам lezer
		const customHighlighter = (tag) => tokenClasses[tag] || null;

		// Сохраняем парсер для этого языка
		this.languages[langName] = {
			parser: languageObject.streamParser,
			tokenClasses,
			tokenNameToClass,
			customHighlighter
		};
	}

	/**
	 * Генерирует строку со всеми CSS стилями темы
	 */
	getCssCode() {
		return `
      .cm-editor { background: #f8f9fa; padding: 14px; border-radius: 6px; border: 1px solid #e9ecef; overflow-x: auto; margin: 15px 0; }
      .cm-scroller { font-family: "Fira Code", Monaco, Consolas, monospace; font-size: 14px; white-space: pre; line-height: 1.5; }
      ${this.cssRules.join('\n')}
    `;
	}

	/**
	 * Внутренний метод экранирования HTML
	 */
	_escapeHtml(text) {
		return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
	}

	/**
	 * Функция для интеграции в Markdown-it
	 */
	getMarkdownHighlightFunction() {
		return (code, lang) => {
			const langConfig = this.languages[lang];
			if (!langConfig) return ''; // Если язык не зарегистрирован, markdown-it отработает по дефолту

			try {
				let htmlResult = "";
				const lines = code.split('\n');
				let state = langConfig.parser.startState();

				lines.forEach((line, index) => {
					// Пропускаем последнюю пустую строку, которую часто добавляет редактор
					if (line === "" && index === lines.length - 1) return;

					const stream = new StringStream(line);

					while (!stream.eol()) {
						const start = stream.pos;
						const tokenType = langConfig.parser.token(stream, state);
						const text = line.slice(start, stream.pos);

						// Пытаемся найти CSS-класс по имени токена или по объекту тега
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