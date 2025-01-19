let offset = 10; // Начальный offset для кол-ва кинотеатров
let searchTerm = ''; // Текущий поисковый запрос
let locationFilter = ''; // Фильтр по местоположению
let affiliationFilter = ''; // Фильтр по типу кинотеатра
let minReviews = 0; // Минимальное количество отзывов
let sortOrder = ''; // По умолчанию сортировка не выбрана

// Функция для округления чисел
function round(value, decimals) {
    return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
}

// Функция для загрузки следующих кинотеатров
function loadMore() {
    const url = `search.php?search=${encodeURIComponent(searchTerm)}&location=${encodeURIComponent(locationFilter)}&affiliation=${encodeURIComponent(affiliationFilter)}&min_reviews=${minReviews}&sort=${sortOrder}&offset=${offset}&ajax=1`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const catalog = document.getElementById('catalog');
                data.forEach(cinema => {
                    const photoData = JSON.parse(cinema.photo);
                    const cinemaCard = `
                        <div class="cinema-card">
                            <img src="${photoData.url}" alt="${photoData.title}" class="cinema-photo">
                            <div class="cinema-info">
                                <h3>${cinema.name}</h3>
                                <p>${cinema.location}</p>
                                <p>Отзывы: ${cinema.review_count} | Средняя оценка: ${cinema.avg_score ? round(cinema.avg_score, 1) : 'Нет данных'}</p>
                            </div>
                            <button class="details-button" data-id="${cinema.id}">Подробнее</button>
                        </div>
                    `;
                    catalog.insertAdjacentHTML('beforeend', cinemaCard);
                });

                offset += 10;
            } else {
                document.getElementById('load-more-button').style.display = 'none';
            }
        })
        .catch(error => console.error('Ошибка при загрузке кинотеатров:', error));
}

// Функция для загрузки данных с учетом сортировки
function loadData() {
    const url = `search.php?search=${encodeURIComponent(searchTerm)}&location=${encodeURIComponent(locationFilter)}&affiliation=${encodeURIComponent(affiliationFilter)}&min_reviews=${minReviews}&sort=${sortOrder}&offset=0&ajax=1`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const catalog = document.getElementById('catalog');
            catalog.innerHTML = '';

            // Добавление отсортированных кинотеатров
            data.forEach(cinema => {
                const photoData = JSON.parse(cinema.photo);
                const cinemaCard = `
                    <div class="cinema-card">
                        <img src="${photoData.url}" alt="${photoData.title}" class="cinema-photo">
                        <div class="cinema-info">
                            <h3>${cinema.name}</h3>
                            <p>${cinema.location}</p>
                            <p>Отзывы: ${cinema.review_count} | Средняя оценка: ${cinema.avg_score ? round(cinema.avg_score, 1) : 'Нет данных'}</p>
                        </div>
                        <button class="details-button" data-id="${cinema.id}">Подробнее</button>
                    </div>
                `;
                catalog.insertAdjacentHTML('beforeend', cinemaCard);
            });

            const loadMoreButton = document.getElementById('load-more-button');
            if (data.length === 10) {
                loadMoreButton.style.display = 'block';
            } else {
                loadMoreButton.style.display = 'none';
            }
        })
        .catch(error => console.error('Ошибка при загрузке данных:', error));
}

// Функция для живого поиска
function handleSearch() {
    const searchInput = document.getElementById('search-input');
    searchTerm = searchInput.value.trim();

    if (searchTerm.length >= 2 || searchTerm.length === 0) {
        const url = `search.php?search=${encodeURIComponent(searchTerm)}&location=${encodeURIComponent(locationFilter)}&affiliation=${encodeURIComponent(affiliationFilter)}&min_reviews=${minReviews}&sort=${sortOrder}&ajax=1`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const catalog = document.getElementById('catalog');
                catalog.innerHTML = '';

                data.forEach(cinema => {
                    const photoData = JSON.parse(cinema.photo);
                    const cinemaCard = `
                        <div class="cinema-card">
                            <img src="${photoData.url}" alt="${photoData.title}" class="cinema-photo">
                            <div class="cinema-info">
                                <h3>${cinema.name}</h3>
                                <p>${cinema.location}</p>
                                <p>Отзывы: ${cinema.review_count} | Средняя оценка: ${cinema.avg_score ? round(cinema.avg_score, 1) : 'Нет данных'}</p>
                            </div>
                            <button class="details-button" data-id="${cinema.id}">Подробнее</button>
                        </div>
                    `;
                    catalog.insertAdjacentHTML('beforeend', cinemaCard);
                });

                const loadMoreButton = document.getElementById('load-more-button');
                if (data.length === 10) {
                    loadMoreButton.style.display = 'block';
                } else {
                    loadMoreButton.style.display = 'none';
                }

                offset = 10;
            })
            .catch(error => console.error('Ошибка при поиске:', error));
    }
}

// Функция для применения фильтров
function applyFilters() {
    locationFilter = document.getElementById('location-filter').value;
    affiliationFilter = document.getElementById('affiliation-filter').value;
    minReviews = parseInt(document.getElementById('min-reviews').value, 10);

    handleSearch();
}

// Назначаем обработчики событий
document.getElementById('search-input').addEventListener('input', handleSearch);
document.getElementById('load-more-button').addEventListener('click', loadMore);
document.getElementById('filter-button').addEventListener('click', function () {
    const filters = document.getElementById('filters');
    filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
});

// Обработчик для кнопки "Подробнее" с использованием делегирования событий
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('details-button')) {
        const cinemaId = event.target.getAttribute('data-id');
        if (cinemaId) {
            window.location.href = `cinema.php?id=${cinemaId}`;
        }
    }
});

// Обработчик изменения сортировки
document.getElementById('sort-select').addEventListener('change', function (event) {
    sortOrder = event.target.value;
    offset = 0;
    loadData();
});

// Показать/скрыть выпадающее меню пользователя
document.getElementById('user-menu').addEventListener('click', function (event) {
    event.preventDefault(); // Отменяем стандартное поведение ссылки
    const dropdown = document.getElementById('user-dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});

// Скрыть меню при клике вне его
document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('user-dropdown');
    const userMenu = document.getElementById('user-menu');
    if (dropdown && userMenu && !userMenu.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});