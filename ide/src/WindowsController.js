

export class WindowsController {
	_activeResizer = null;
	_startPos = 0;
	_firstSize = 0;
	_currentPercent = 0;

	constructor() {
		this._bind();
		this._loadSizes();
	}

	_bind() {
		document.querySelectorAll('.resizer').forEach((el) => {
			el.addEventListener('mousedown', this._onMouseDown);
		})

		document.addEventListener('mousemove', this._onMouseMove);
		document.addEventListener('mouseup', this._onMouseUp);
	}

	_onMouseDown = (e) => {
		this._activeResizer = e.currentTarget;

		if (this._isHorizontal()) {
			this._startPos = e.clientY;
			this._firstSize = this._activeResizer.previousElementSibling.getBoundingClientRect().height;
		} else {
			this._startPos = e.clientX;
			this._firstSize = this._activeResizer.previousElementSibling.getBoundingClientRect().width;
		}

		this._startDrag();
	}

	_onMouseMove = (e) => {
		if (this._activeResizer === null) { return; }

		if (this._isHorizontal()) {
			const dy = e.clientY - this._startPos;

			const containerHeight = this._activeResizer.parentNode.getBoundingClientRect().height;
			this._currentPercent = ((this._firstSize + dy) / containerHeight) * 100;

			this._activeResizer.previousElementSibling.style.height = `${this._currentPercent}%`;
		} else {
			const dx = e.clientX - this._startPos;

			const containerWidth = this._activeResizer.parentNode.getBoundingClientRect().width;
			this._currentPercent = ((this._firstSize + dx) / containerWidth) * 100;

			this._activeResizer.previousElementSibling.style.width = `${this._currentPercent}%`;
		}
	}

	_onMouseUp = (e) => {
		if (this._activeResizer === null) { return; }

		if (this._currentPercent !== null) {
			this._saveSize(this._activeResizer.id, this._currentPercent);
		}

		this._stopDrag();
		this._activeResizer = null;
	}

	_startDrag() {
		this._activeResizer.classList.add('--active');
		document.body.style.cursor = 'grabbing';
		document.body.style.userSelect = 'none';
	}

	_stopDrag() {
		this._activeResizer.classList.remove('--active');
		document.body.style.removeProperty('cursor');
		document.body.style.removeProperty('user-select');
	}

	_isHorizontal() {
		return this._activeResizer.classList.contains('--horizontal');
	}

	_saveSize(id, percent) {
		if (!id) { return; }
		localStorage.setItem(`window-size-${id}`, percent);
	}

	_loadSizes() {
		document.querySelectorAll('.resizer').forEach((el) => {
			if (!el.id) return;

			const savedPercent = localStorage.getItem(`window-size-${el.id}`);
			if (savedPercent && el.previousElementSibling) {
				const target = el.previousElementSibling;

				if (el.classList.contains('--horizontal')) {
					target.style.height = `${savedPercent}%`;
				} else {
					target.style.width = `${savedPercent}%`;
				}
			}
		});
	}
}