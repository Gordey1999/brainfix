import fs from 'fs';
import path from 'path';
import MarkdownIt from 'markdown-it';
import { DocHighlighter } from './DocHighlighter.js';
import {bfLanguage, bfHighlight} from '../public/src/lib/bf-lang.mjs';
import {bfxLanguage, bfxHighlight} from '../public/src/lib/bfx-lang.mjs';

// 1. Создаем экземпляр подсвечивателя и регистрируем языки
const highlighter = new DocHighlighter();

highlighter.registerLanguage('brainfuck', bfLanguage, bfHighlight);
highlighter.registerLanguage('brainfix', bfxLanguage, bfxHighlight);

// 2. Инициализируем Markdown-it и передаем ему метод нашего класса
const md = new MarkdownIt({
	html: true,
	highlight: highlighter.getMarkdownHighlightFunction()
});

// 3. Главная функция сборки
function build() {
	const srcDir = './docs-src';
	const outDir = './docs';

	// Убедимся, что папка docs существует
	if (!fs.existsSync(outDir)) {
		fs.mkdirSync(outDir, {recursive: true});
	}

	fs.writeFileSync(path.join(outDir, 'theme.css'), highlighter.getCssCode());

	// Читаем исходный Markdown и HTML-шаблон
	const markdownInput = fs.readFileSync(path.join(srcDir, 'index.md'), 'utf-8');
	const template = fs.readFileSync(path.join(srcDir, 'template.html'), 'utf-8');

	// Переводим Markdown в HTML
	const mainContentHtml = md.render(markdownInput);

	// Вставляем контент в шаблон
	const finalHtml = template
		.replace('<!-- CONTENT -->', mainContentHtml);

	// Записываем готовый результат в /docs/index.html для GitHub Pages
	fs.writeFileSync(path.join(outDir, 'index.html'), finalHtml);
	console.log('🎉 Документация успешно собрана в папку /docs!');
}

build();