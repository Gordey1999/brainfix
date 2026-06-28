import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import terser from "@rollup/plugin-terser";
import license from 'rollup-plugin-license';

export default {
	input: 'ide/src/index.mjs',

	output: {
		file: 'docs/index.bundle.js',
		format: 'iife',
		sourcemap: false,
	},
	plugins: [
		// Позволяет импортировать библиотеки из node_modules
		nodeResolve(),
		// Превращает CommonJS код (как у localforage) в понятный для Rollup формат
		commonjs(),
		// Активирует минимизацию JS
		terser(),
		// Лицензии
		license({
			thirdParty: {
				output: 'docs/vendor-licenses.txt',
				includePrivate: false,
			},
		}),
	]
};