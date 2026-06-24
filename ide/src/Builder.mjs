
export class Builder {
	_ajaxUrl = '';
	_uglify = false;

	constructor(editor, console, ajaxUrl) {
		this._editor = editor;
		this._console = console;
		this._ajaxUrl = ajaxUrl;
	}

	setTabManager(tabManager) {
		this._tabManager = tabManager;
	}

	onBuild = () => {
		this._console.clear();
		this._console.setStatus('building');
		this._build();
	}

	onBuildMin = () => {
		this._console.clear();
		this._console.setStatus('building');
		this._build(true);
	}

	onUglify = (e) => {
		const toggle = e.currentTarget.querySelector('.btn-toggle');
		const isActive = toggle.classList.contains('--active');
		this._uglify = !isActive;
		toggle.classList.toggle('--active', !isActive);
	}

	async _build(min = false) {
		const code = this._editor.getCode();
		const title = this._tabManager.getTitle(code);

		try {
			const response = await this._query(code, title, min, this._uglify);

			if (!response.ok) {
				this._showError('Brainfix compile error:\nSTATUS ' + response.status);
				return;
			}

			const textData = await response.text();

			try {
				const jsonData = JSON.parse(textData);

				if (jsonData.status === 'ok') {
					this._render(jsonData.result, jsonData.log);
				} else {
					this._showError(jsonData.message, jsonData.position);
				}
			} catch (e) {
				this._showError('cant parse json');
			}

		} catch (error) {
			this._showError("Brainfix compile server is temporary unavailable.\nTry later");
		}
	}

	_query(code, title, min = false, uglify = false) {
		return fetch(this._ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				title: title,
				code: code,
				min: min,
				uglify: uglify,
			})
		})
	}

	_render(result, log) {
		this._editor.highlightError();
		this._console.echo(log);
		this._tabManager.showCompiled(result);
		this._console.setStatus('finished');
	}

	_showError(message, position) {
		this._console.showError(message);
		if (position) {
			this._editor.highlightError(position.start, position.length);
		}
	}
}