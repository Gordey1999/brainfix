
// MENU LINKS
document.addEventListener('DOMContentLoaded', () => {
	const submenuLinks = document.querySelectorAll('.doc-submenu-link');
	const headers = document.querySelectorAll('.doc-main h1[id], .doc-main h2[id]');

	if (!headers.length || !submenuLinks.length) return;

	let isClickScrolling = false;
	let clickTimeout;

	const activateLink = (id) => {
		submenuLinks.forEach((link) => link.classList.remove('--active'));

		const activeLink = document.querySelector(`.doc-submenu-link[href="#${id}"]`);

		if (activeLink) {
			activeLink.classList.add('--active');
		}
	};

	submenuLinks.forEach(link => {
		link.addEventListener('click', (e) => {
			const id = link.getAttribute('href').substring(1);
			const targetHeader = document.getElementById(id);

			if (targetHeader) {
				e.preventDefault();
				isClickScrolling = true;
				clearTimeout(clickTimeout);

				activateLink(id);

				const topOffset = targetHeader.getBoundingClientRect().top + window.scrollY - 20;
				window.scrollTo({
					top: topOffset,
					behavior: "smooth"
				});

				clickTimeout = setTimeout(() => {
					isClickScrolling = false;
				}, 1500);
			}
		});
	});

	const observerOptions = {
		root: null,
		rootMargin: "-10% 0px -70% 0px",
		threshold: 0
	};

	const observerCallback = (entries) => {
		if (isClickScrolling) return;

		const visibleHeaders = [];

		headers.forEach(header => {
			const rect = header.getBoundingClientRect();
			if (rect.top > 0 && rect.top < window.innerHeight * 0.3) {
				visibleHeaders.push(header);
			}
		});

		if (visibleHeaders.length > 0) {
			activateLink(visibleHeaders[0].id);
		} else {
			let closestHeader = null;
			let minDistance = Infinity;

			headers.forEach(header => {
				const rect = header.getBoundingClientRect();
				// Заголовок выше зоны считывания (ушел вверх)
				if (rect.top <= 0) {
					const distance = Math.abs(rect.top);
					if (distance < minDistance) {
						minDistance = distance;
						closestHeader = header;
					}
				}
			});

			if (closestHeader) {
				activateLink(closestHeader.id);
			}
		}
	};

	const observer = new IntersectionObserver(observerCallback, observerOptions);
	headers.forEach((header) => observer.observe(header));
});

// COPY CODE BUTTON
document.querySelectorAll('.doc-code-block').forEach((wrapper) => {

	const button = wrapper.querySelector('.doc-code-header__copy');
	const codeBlock = wrapper.querySelector('code');
	if (!codeBlock || !button) return;

	button.addEventListener('click', async () => {
		try {
			await navigator.clipboard.writeText(codeBlock.innerText);

			button.innerText = 'скопировано!';

			setTimeout(() => {
				button.innerText = 'скопировать';
			}, 1500);
		} catch (err) {
			console.error('Не удалось скопировать код: ', err);
		}
	});
});