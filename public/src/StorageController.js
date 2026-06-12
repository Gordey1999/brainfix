

export class StorageController {
	constructor(saveModal, loadModal, storage, tabManager) {
		this._saveModal = saveModal;
		this._loadModal = loadModal;
		this._storage = storage;
		this._tabManager = tabManager;

		this._bind();
	}

	_bind() {
		this._saveModal.querySelector('.modal-header-close .link').addEventListener('click', this.onClose);
		this._loadModal.querySelector('.modal-header-close .link').addEventListener('click', this.onClose);
		this._saveModal.addEventListener('click', this.onModalClick.bind(this, this._saveModal));
		this._loadModal.addEventListener('click', this.onModalClick.bind(this, this._loadModal));

		this._saveModal.querySelector('.link-new-slot').addEventListener('click', this.onNewSlot);
		this._saveModal.querySelector('.link-export').addEventListener('click', this.onExport);
		this._saveModal.querySelector('.link-download').addEventListener('click', this.onDownload);
		this._loadModal.querySelector('.link-import').addEventListener('click', this.onImport);
	}

	onSave = () => {
		this._saveModal.classList.add('--active');
		this._renderSaveSlots();
	}

	onLoad = () => {
		this._loadModal.classList.add('--active');
		this._renderLoadSlots();
	}

	onClose = () => {
		this._saveModal.classList.remove('--active');
		this._loadModal.classList.remove('--active');
	}

	onModalClick = (modal, e) => {
		if (e.target === modal) {
			this.onClose();
		}
	}

	onNewSlot = async () => {
		const userInput = prompt("input new slot name:", "new slot");

		if (userInput !== null && userInput.trim() !== "") {
			const id = await this._storage.addSlot(userInput.trim());
			const data = await this._tabManager.getStateForSave();
			await this._storage.save(id, data);
			await this._renderSaveSlots();
		}
	}

	onSlotSave = async (slotId) => {
		const isConfirmed = confirm('Are you sure you want to overwrite this save?');

		if (isConfirmed) {
			const data = await this._tabManager.getStateForSave();
			await this._storage.save(slotId, data);
			await this._renderSaveSlots();
		}
	}

	onSlotLoad = async (slotId) => {
		const isConfirmed = confirm('Are you sure you want to close this project?');

		if (isConfirmed) {
			const data = await this._storage.load(slotId);
			this._tabManager.setStateFromSave(data);
			await this._renderSaveSlots();
			this.onClose();
		}
	}

	onSlotRename = async (slotId, oldName) => {
		const userInput = prompt("input new slot name:", oldName);

		if (userInput !== null && userInput.trim() !== "") {
			await this._storage.renameSlot(slotId, userInput.trim());
			await this._renderSaveSlots();
			await this._renderLoadSlots();
		}
	}

	onSlotDelete = async (slotId) => {
		const isConfirmed = confirm('Are you sure you want to delete this save?');

		if (isConfirmed) {
			await this._storage.deleteSlot(slotId);
			await this._renderSaveSlots();
			await this._renderLoadSlots();
		}
	}

	onExport = async () => {
		const data = await this._tabManager.getStateForSave();

		if ('showSaveFilePicker' in window) {
			await this._exportWithPicker(data);
		} else {
			alert('Your browser doesn\'t support file picker! Use download button.');
		}
	}

	onDownload = async () => {
		const data = await this._tabManager.getStateForSave();
		await this._exportWithBlob(data);
	}

	onImport = async () => {
		let loadedData = null;

		if ('showOpenFilePicker' in window) {
			loadedData = await this._importWithPicker();
		} else {
			loadedData = await this._importWithInput();
		}

		if (loadedData) {
			try {
				await this._tabManager.setStateFromSave(loadedData);
				this.onClose();
			} catch (err) {
				alert('Error importing project.');
				console.error(err);
			}
		}
	}

	async _exportWithPicker(data) {
		const jsonString = JSON.stringify(data, null, 2);

		try {
			const handle = await window.showSaveFilePicker({
				suggestedName: 'project.bfp',
				types: [{
					description: 'Brainfuck Project File (.bfp)',
					accept: {
						'application/json': ['.bfp']
					}
				}]
			});

			const writable = await handle.createWritable();
			await writable.write(jsonString);
			await writable.close();

			alert('Saved successfully.');
		} catch (err) {
			if (err.name !== 'AbortError') {
				console.error('Ошибка при сохранении:', err);
			}
		}
	}

	async _exportWithBlob(data) {
		const jsonString = JSON.stringify(data, null, 2);

		const blob = new Blob([jsonString], { type: 'application/json' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url;
		a.download = 'project.bfp';
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);
	}

	async _importWithPicker() {
		try {
			const [handle] = await window.showOpenFilePicker({
				types: [{
					description: 'Brainfuck Project File (.bfp)',
					accept: {
						'application/json': ['.bfp']
					}
				}],
				multiple: false
			});

			const file = await handle.getFile();
			const text = await file.text();
			return JSON.parse(text);
		} catch (err) {
			if (err.name !== 'AbortError') {
				console.error('Ошибка при импорте:', err);
				alert('Failed to read file.');
			}
			return null;
		}
	}

	async _importWithInput() {
		return new Promise((resolve) => {
			const input = document.createElement('input');
			input.type = 'file';
			input.accept = '.bfp';

			input.onchange = async (event) => {
				const file = event.target.files[0];
				if (!file) {
					resolve(null);
					return;
				}

				try {
					const text = await file.text();
					resolve(JSON.parse(text));
				} catch (err) {
					alert('Invalid file format.');
					resolve(null);
				}
			};

			input.click();
		});
	}

	async _renderSaveSlots() {
		let slots = await this._storage.getSlots();

		const slotsEl = this._saveModal.querySelector('.saves');
		slotsEl.innerHTML = '';

		const template = this._saveModal.querySelector('.saves-row-template');

		for (let slot of slots) {
			const row = template.content.cloneNode(true);

			row.querySelector('.saves-row__title').textContent = slot.name;
			row.querySelector('.saves-row__time').textContent = `(${slot.timeAgo})`;

			row.querySelector('.link-save').addEventListener('click', this.onSlotSave.bind(this, slot.id));
			row.querySelector('.link-rename').addEventListener('click', this.onSlotRename.bind(this, slot.id, slot.name));
			row.querySelector('.link-delete').addEventListener('click', this.onSlotDelete.bind(this, slot.id));

			slotsEl.appendChild(row);
		}
	}

	async _renderLoadSlots() {
		let slots = await this._storage.getSlots();

		const slotsEl = this._loadModal.querySelector('.saves');
		slotsEl.innerHTML = '';

		const template = this._loadModal.querySelector('.saves-row-template');

		for (let slot of slots) {
			const row = template.content.cloneNode(true);

			row.querySelector('.saves-row__title').textContent = slot.name;
			row.querySelector('.saves-row__time').textContent = `(${slot.timeAgo})`;

			row.querySelector('.link-load').addEventListener('click', this.onSlotLoad.bind(this, slot.id));
			row.querySelector('.link-rename').addEventListener('click', this.onSlotRename.bind(this, slot.id, slot.name));
			row.querySelector('.link-delete').addEventListener('click', this.onSlotDelete.bind(this, slot.id));

			slotsEl.appendChild(row);
		}
	}
}