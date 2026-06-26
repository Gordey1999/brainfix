document.addEventListener("DOMContentLoaded", () => {
	const submenuLinks = document.querySelectorAll(".doc-submenu-link");
	const headers = document.querySelectorAll(".doc-main h1[id], .doc-main h2[id], .doc-main h3[id]");

	if (!headers.length || !submenuLinks.length) return;

	// Флаг, который блокирует автоматическую смену от скролла во время анимации клика
	let isClickScrolling = false;
	let clickTimeout;

	// Функция для переключения активного класса на нужную ссылку
	const activateLink = (id) => {
		submenuLinks.forEach((link) => link.classList.remove("--active"));
		const activeLink = document.querySelector(`.doc-submenu-link[href="#${id}"]`);
		if (activeLink) activeLink.classList.add("--active");
	};

	// 1. ОБРАБОТКА КЛИКОВ: Плавный скролл и мгновенный фокус
	submenuLinks.forEach(link => {
		link.addEventListener("click", (e) => {
			const id = link.getAttribute("href").substring(1);
			const targetHeader = document.getElementById(id);

			if (targetHeader) {
				e.preventDefault(); // Отменяем резкий системный прыжок
				isClickScrolling = true; // Блокируем скролл-обсервер
				clearTimeout(clickTimeout);

				// Сразу зажигаем лампочку/активируем пункт меню
				activateLink(id);

				// Плавно катим страницу к заголовку с учетом отступа сверху (например, 20px)
				const topOffset = targetHeader.getBoundingClientRect().top + window.scrollY - 20;
				window.scrollTo({
					top: topOffset,
					behavior: "smooth"
				});

				// Возвращаем контроль обсерверу, когда плавная прокрутка завершится (через 600мс)
				clickTimeout = setTimeout(() => {
					isClickScrolling = false;
				}, 600);
			}
		});
	});

	// 2. ОБРАБОТКА СКРОЛЛА: Слежка за чтением
	const observerOptions = {
		root: null,
		// Настраиваем зону: триггер сработает, когда заголовок в промежутке от 15% до 60% высоты экрана
		rootMargin: "-15% 0px -60% 0px",
		threshold: 0
	};

	const observerCallback = (entries) => {
		// Если скролл вызван кликом по меню — игнорируем обсервер, чтобы ничего не мигало
		if (isClickScrolling) return;

		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				const id = entry.target.getAttribute("id");
				activateLink(id);
			}
		});
	};

	const observer = new IntersectionObserver(observerCallback, observerOptions);
	headers.forEach((header) => observer.observe(header));
});