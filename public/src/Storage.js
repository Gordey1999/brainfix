import localforage from "localforage"

export class Storage {
	constructor() {

	}

	async getSlots() {
		let slots = await localforage.getItem('save_slots') || {};

		return Object.values(slots).map(slot => ({
			id: slot.id,
			name: slot.name,
			updatedAt: slot.updatedAt,
			timeAgo: this._formatRelativeTime(slot.updatedAt)
		})).reverse();
	}

	async addSlot(slotName) {
		try {
			const slotsList = await localforage.getItem('save_slots') || {};
			const newId = Math.max(0, ...Object.keys(slotsList)) + 1;

			slotsList[newId] = {
				id: newId,
				name: slotName,
				updatedAt: Date.now(),
			};

			await localforage.setItem('save_slots', slotsList);

			return newId;
		} catch (err) {
			alert('Ошибка при сохранении:'+ err);
		}
	}

	async renameSlot(slotId, newName) {
		try {
			const slotsList = await localforage.getItem('save_slots') || {};

			slotsList[slotId].name = newName;
			await localforage.setItem('save_slots', slotsList);
		} catch (err) {
			alert('Ошибка при удалении:' + err);
		}
	}

	async updateSlotTime(slotId, updatedAt) {
		try {
			const slotsList = await localforage.getItem('save_slots') || {};

			slotsList[slotId].updatedAt = updatedAt;
			await localforage.setItem('save_slots', slotsList);
		} catch (err) {
			alert('Ошибка при сохранении:' + err);
		}
	}

	async deleteSlot(slotId) {
		try {
			const slotsList = await localforage.getItem('save_slots') || {};

			delete slotsList[slotId];
			await localforage.setItem('save_slots', slotsList);

			await localforage.removeItem(`save_slot_${slotId}`);
		} catch (err) {
			alert('Ошибка при сохранении:'+ err);
		}
	}

	async save(slotId, data) {
		try {
			await localforage.setItem(`save_slot_${slotId}`, data);

			await this.updateSlotTime(slotId, Date.now());
		} catch (err) {
			alert('Ошибка при сохранении:'+ err);
		}
	}

	async load(slotId) {
		try {
			return await localforage.getItem(`save_slot_${slotId}`) || {};
		} catch (err) {
			alert('Ошибка при загрузке:'+ err);
		}
	}

	_formatRelativeTime(timestamp) {
		if (!timestamp) return 'never';

		const msPerMinute = 60 * 1000;
		const msPerHour = msPerMinute * 60;
		const msPerDay = msPerHour * 24;

		const elapsed = timestamp - Date.now();
		const absElapsed = Math.abs(elapsed);

		const rtf = new Intl.RelativeTimeFormat('en', { style: 'narrow', numeric: 'always' });

		if (absElapsed < 5000) {
			return 'now'; // Если прошло меньше 5 секунд
		} else if (absElapsed < msPerMinute) {
			return rtf.format(Math.round(elapsed / 1000), 'second'); // "15s ago"
		} else if (absElapsed < msPerHour) {
			return rtf.format(Math.round(elapsed / msPerMinute), 'minute'); // "10m ago"
		} else if (absElapsed < msPerDay) {
			return rtf.format(Math.round(elapsed / msPerHour), 'hour'); // "2h ago"
		} else {
			return rtf.format(Math.round(elapsed / msPerDay), 'day'); // "1d ago"
		}
	}
}