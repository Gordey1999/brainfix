import {MetaParser} from "./lib/MetaParser.js";

export class TabManager {

	constructor(element, controller, builder, editor, input) {
		this._el = element;
		this._controller = controller;
		this._builder = builder;
		this._editor = editor;
		this._input = input;
		this._tabData = [];
		this._tabIdCounter = 0;
		this._fillTitleTimeout = null;

		this._bind();
		this._init();
	}

	showCompiled(code) {
		const parent = this._getActiveTab();

		this._updateActiveTabData();
		const tabData = this._getTabData(parent);

		const children = this._getChildTabs(parent);

		if (children.length > 0) {
			this._closeTab(children[0]);
		}

		this._addTab(true, parent, code, tabData.input);
	}

	async getStateForSave() {
		this._updateActiveTabData();

		return this._tabData
			.filter((tab) => !tab.isSubtab)
			.map((tab) => {
				return {
					code: this._editor.getStateCode(tab.tabId),
					input: tab.input,
					language: tab.language,
					isSubtab: tab.isSubtab,
				};
			});
	}

	async setStateFromSave(data) {
		this._closeAll();

		let active = null;
		let lastParent = null;
		for (const tab of data) {

			const tabData = this._createTab(
				tab.language,
				tab.isSubtab ? lastParent : null,
				tab.code,
				tab.input,
				tab?.editor ?? null
			);

			if (!tab.isSubtab) {
				lastParent = tabData.el;
			}

			if (tab?.active) {
				active = tabData;
			}
		}

		this._setActiveTab(active ? active.el : this._tabData[0].el);
	}

	async getFullState() {
		this._updateActiveTabData();

		return this._tabData.map((tab) => {
			return {
				code: this._editor.getStateCode(tab.tabId),
				input: tab.input,
				language: tab.language,
				isSubtab: tab.isSubtab,
				editor: this._editor.getSerializableState(tab.tabId),
				active: tab.el === this._getActiveTab(),
			};
		});
	}

	async setFullState(state) {
		await this.setStateFromSave(state);
	}

	onAddTab(language) {
		const title = this.getTitle('', language);
		const code = `# @title: ${title}\n\n`;
		this._addTab(language === 'bf', null, code, '');
	}

	onEditorChange = () => {
		if (this._fillTitleTimeout) {
			clearTimeout(this._fillTitleTimeout);
		}

		this._fillTitleTimeout = setTimeout(this._setTitle.bind(this), 1000);
	}

	async _init() {
		const code = "# title: Hello\n\n out 'Hello, World!'";
		this._addTab(false, null, code);
	}

	_bind() {
		this._el.querySelector('.tab-plus')
			.addEventListener('click', this.onAddTab.bind(this, 'bb'));
		this._el.querySelector('.tab-plus-bf')
			.addEventListener('click', this.onAddTab.bind(this, 'bf'));
	}

	_setTitle() {
		const activeTab = this._getActiveTab();
		if (!activeTab) { return; }

		const code = this._editor.getCode();
		activeTab.querySelector('.tab-name').textContent = this.getTitle(code, activeTab.language);
	}

	getTitle(code, language) {
		const defaultName = language === 'bf' ? 'untitled.bf' : 'untitled';
		return MetaParser.getHeaderValue(code, 'title', defaultName);
	}

	_createTab(language, parent = null, code = '', input = '', editor = null) {
		const el = document.createElement('div');
		const name = document.createElement('span');
		const close = document.createElement('span');

		el.classList.add('tab');
		name.classList.add('tab-name');
		close.classList.add('tab-close');

		name.textContent = this.getTitle(code);
		close.textContent = 'x';

		if (language === 'bf') {
			el.classList.add('tab-bf');
		}
		if (parent) {
			el.classList.add('tab-subtab');
		}

		el.appendChild(name);
		el.appendChild(close);

		if (parent) {
			parent.after(el);
		} else {
			this._el.querySelector('.tab-plus').before(el);
		}

		const tabId = this._tabIdCounter++;
		if (editor) {
			this._editor.setSerializableState(tabId, language, code, editor);
		} else {
			this._editor.addState(tabId, code, language);
		}

		const tab = {
			el: el,
			tabId: tabId,
			input: input,
			inputActive: input.length > 0,
			language: language,
			isSubtab: !!parent,
		};

		if (parent) {
			const parentIndex = this._tabData.indexOf(this._getTabData(parent));
			this._tabData.splice(parentIndex + 1, 0, tab);
		} else {
			this._tabData.push(tab);
		}

		el.addEventListener('click', this._setActiveTab.bind(this, el));
		close.addEventListener('click', this._closeTab.bind(this, el));

		return tab;
	}

	_addTab(bf = false, parent = null, code = '', input = '') {
		const tab = this._createTab(bf ? 'bf' : 'bb', parent, code, input);
		this._setActiveTab(tab.el);
	}

	_setActiveTab(el) {
		const activeTab = this._getActiveTab();
		if (activeTab === el) { return; }

		this._setTitle();
		this._updateActiveTabData();
		activeTab?.classList.remove('--active');
		this._controller.onStop();

		const tabData = this._getTabData(el);
		this._setButtons(tabData.language);
		this._editor.switchState(tabData.tabId);
		this._input.set(tabData.input);
		this._input.setActive(tabData.inputActive);

		el.classList.add('--active');
	}

	_updateActiveTabData() {
		const activeTab = this._getActiveTab();

		if (activeTab) {
			const tabData = this._getTabData(activeTab);
			tabData.input = this._input.getRaw();
			tabData.inputActive = this._input.isActive();
		}
	}

	_getChildTabs(el) {
		const result = [];
		let last = el;
		while (true) {
			const tab = last.nextElementSibling;
			if (!tab.classList.contains('tab-subtab')) {
				break;
			}
			result.push(tab);
			last = tab;
		}

		return result;
	}

	_getActiveTab() {
		return this._el.querySelector('.tab.--active');
	}

	_setButtons(language) {
		if (language === 'bf') {
			document.querySelector('.buttons-bf').classList.add('--active');
			document.querySelector('.buttons-bb').classList.remove('--active');
		} else {
			document.querySelector('.buttons-bb').classList.add('--active');
			document.querySelector('.buttons-bf').classList.remove('--active');
		}
	}

	_closeTab(el, e) {
		e?.stopPropagation();

		const children = this._getChildTabs(el);
		if (children.length > 0) {
			for (const child of children) {
				this._closeTab(child);
			}
		}

		if (this._el.querySelectorAll('.tab').length <= 3) { return; }

		const activeTab = this._getActiveTab();
		if (activeTab === el) {
			if (el.previousElementSibling) {
				this._setActiveTab(el.previousElementSibling);
			} else if(el.nextElementSibling) {
				this._setActiveTab(el.nextElementSibling);
			}
		}

		this._removeTabData(el);
		el.remove();
	}

	_getTabData(el) {
		for (const tab of this._tabData) {
			if (tab.el === el) { return tab; }
		}
		return null;
	}

	_removeTabData(el) {
		for (const i in this._tabData) {
			if (this._tabData[i].el === el) {
				this._editor.removeState(this._tabData[i].tabId);
				this._tabData.splice(i, 1);
			}
		}
	}

	_closeAll() {
		for (const tab of this._tabData) {
			tab.el.remove();
		}
		this._tabData = [];
		this._editor.clearStates();
	}
}