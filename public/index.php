

<link rel="stylesheet" href="style.css"/>

<div class="container">
	<div class="nav">
		<div class="buttons buttons-bf">
			<div class="buttons-block">
				<button class="btn btn-run">run</button>
				<button class="btn btn-stop">stop</button>
			</div>
			<div class="buttons-block">
				<button class="btn btn-step">step</button>
				<button class="btn btn-line">line</button>
				<button class="btn btn-out">out</button>
			</div>
			<div class="buttons-block">
				<button class="btn btn-input">input</button>
			</div>
			<div class="buttons-block">
				<button class="btn btn-save">save</button>
				<button class="btn btn-load">load</button>
			</div>
		</div>
		<div class="buttons buttons-bb">
			<div class="buttons-block">
				<button class="btn btn-build">build</button>
				<button class="btn btn-build-min">min</button>
				<button class="btn btn-uglify"><span class="btn-toggle">•</span>ugly</button>
			</div>
			<div class="buttons-block">
				<button class="btn btn-input">input</button>
			</div>
			<div class="buttons-block">
				<button class="btn btn-save">save</button>
				<button class="btn btn-load">load</button>
			</div>
		</div>
		<div class="nav-end">Brainfucker 3000
			<div class="nav-end-front">Brainfucker 3000</div>
		</div>
	</div>
	<div class="content">
		<div class="left">
			<div class="tabs">
				<div class="tab tab-plus">+</div>
				<div class="tab tab-bf tab-subtab tab-plus-bf">+</div>
			</div>
			<div class="edit-area block"></div>
		</div>

		<div class="resizer --vertical" id="resizer-main">
			<div class="resizer-inner"></div>
		</div>

		<div class="right">
			<div class="right-top">
				<div class="console block --full-width">
					<div class="console-info">
						<pre class="console-status"></pre>
						<pre class="console-commands"></pre>
					</div>
					<pre class="console-container" tabindex="1"></pre>
				</div>

				<div class="resizer --vertical --hidden" id="resizer-console-input">
					<div class="resizer-inner"></div>
				</div>

				<div class="console-input block">
					<pre class="console-input-textarea" contenteditable="plaintext-only" spellcheck="false"></pre>
				</div>
			</div>

			<div class="resizer --horizontal" id="resizer-console-tracking">
				<div class="resizer-inner"></div>
			</div>

			<div class="tracing block">
				<div class="tracing-container"></div>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-save">
	<div class="modal-content">
		<div class="modal-header">
			<div class="modal-header-title">
				SAVE AS
			</div>
			<div class="modal-header-close">
				<span class="link --red">
					x
				</span>
			</div>
		</div>
		<div class="modal-body">
			<div class="links-row saves-top">
				<span class="link link-new-slot">new slot</span>
			</div>

			<div class="saves">
			</div>

			<template class="saves-row-template">
				<div class="saves-row">
					<div class="saves-row__left">
						<span class="saves-row__title"></span>
						<span class="saves-row__time"></span>
					</div>
					<div class="links-row">
						<div class="link link-save">save</div>
						<div class="link link-rename">rn</div>
						<div class="link --red link-delete">x</div>
					</div>
				</div>
			</template>

			<div class="links-row saves-bottom">
				<div class="link link-export">export</div>
				<div class="link link-download">download</div>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-load">
	<div class="modal-content">
		<div class="modal-header">
			<div class="modal-header-title">
				LOAD
			</div>
			<div class="modal-header-close">
				<span class="link --red">
					x
				</span>
			</div>
		</div>
		<div class="modal-body">

			<div class="saves"></div>

			<template class="saves-row-template">
				<div class="saves-row">
					<div class="saves-row__left">
						<span class="saves-row__title"></span>
						<span class="saves-row__time"></span>
					</div>
					<div class="links-row">
						<div class="link link-load">load</div>
						<div class="link link-rename">rn</div>
						<div class="link --red link-delete">x</div>
					</div>
				</div>
			</template>

			<div class="links-row saves-bottom">
				<div class="link link-import">import</div>
			</div>
		</div>
	</div>
</div>

<script src="index.bundle.js"></script>