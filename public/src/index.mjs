import {Editor} from "./Editor.mjs";
import {Profiler} from "./Profiler.mjs";
import {Console} from "./Console.mjs";
import {FileInput} from "./FileInput.mjs";
import {Controller} from "./Controller.mjs";
import {Builder} from "./Builder.mjs";
import {TabManager} from "./TabManager.mjs";
import {Storage} from "./Storage.js";
import {StorageController} from "./StorageController.js";

// node_modules/.bin/rollup public/src/index.mjs -f iife -o public/index.bundle.js -p @rollup/plugin-node-resolve

const editorEl = document.querySelector('.edit-area');
const profilerEl = document.querySelector('.tracing-container');
const consoleEl = document.querySelector('.console-container');
const statusEl = document.querySelector('.console-status');
const counterEl = document.querySelector('.console-commands');
const input = document.querySelector('.console-input');
const tabs = document.querySelector('.tabs');
const saveModal = document.querySelector('.modal-save');
const loadModal = document.querySelector('.modal-load');

const editor = new Editor(editorEl, '');
const profiler = new Profiler(profilerEl, 500);
const console = new Console(consoleEl, statusEl, counterEl);
const fileInput = new FileInput(input);

const controller = new Controller(editor, profiler, console, fileInput);
const builder = new Builder(editor, console);

const tabManager = new TabManager(tabs, controller, builder, editor, fileInput);

const storage = new Storage();
const storageController = new StorageController(saveModal, loadModal, storage, tabManager);
builder.setTabManager(tabManager);

const nav = document.querySelector('.nav');
const buttonsBb = document.querySelector('.buttons-bb');

nav.querySelector('.btn-run').addEventListener('click', controller.onRun);
nav.querySelector('.btn-stop').addEventListener('click', controller.onStop);
nav.querySelector('.btn-step').addEventListener('click', controller.onStep);
nav.querySelector('.btn-line').addEventListener('click', controller.onStepLine);
nav.querySelector('.btn-out').addEventListener('click', controller.onStepOut);

nav.querySelector('.btn-build').addEventListener('click', builder.onBuild);
nav.querySelector('.btn-build-min').addEventListener('click', builder.onBuildMin);
nav.querySelector('.btn-uglify').addEventListener('click', builder.onUglify)

nav.querySelectorAll('.btn-input').forEach((el) => {
	el.addEventListener('click', fileInput.onToggle);
});
nav.querySelectorAll('.btn-save').forEach((el) => {
	el.addEventListener('click', storageController.onSave)
});
nav.querySelectorAll('.btn-load').forEach((el) => {
	el.addEventListener('click', storageController.onLoad)
});


window.MyEditor = editor;

