import nodeResolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';

export default {
	input: 'public/src/index.mjs',

	output: {
		file: 'public/index.bundle.js',
		format: 'iife',
		sourcemap: true
	},
	plugins: [
		// Позволяет импортировать библиотеки из node_modules
		nodeResolve(),
		// Превращает CommonJS код (как у localforage) в понятный для Rollup формат
		commonjs()
	]
};