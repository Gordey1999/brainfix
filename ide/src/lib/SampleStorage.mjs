
const files = [
	{
		name: 'Игра «САПЕР» (Minesweeper)',
		url: 'sample/saper.bfp',
	},
	{
		name: 'Игра «НИМ»',
		url: 'sample/nim.bfp',
	},
	{
		name: 'Простые примеры',
		url: 'sample/examples.bfp',
	},
	{
		name: 'Home Page',
		url: 'sample/home.bfp',
	}
];

export class SampleStorage {

	static list() {
		return files.map(file => file.name);
	}

	static async load(id) {
		return await this._loadFile(files[id].url);
	}

	static async loadHomePage() {
		return this.load(files.length - 1);
	}

	static async _loadFile(url) {
		const response = await fetch(url);

		if (!response.ok) {
			throw new Error(`download error: ${response.statusText}`);
		}

		return await response.json();
	}
}