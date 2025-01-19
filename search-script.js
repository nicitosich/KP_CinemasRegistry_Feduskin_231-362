let offset = 10; // Начальный offset для кол-ва кинотеатров
let searchTerm = ''; // Текущий поисковый запрос
let locationFilter = ''; // Фильтр по местоположению
let affiliationFilter = ''; // Фильтр по типу кинотеатра
let minReviews = 0; // Минимальное количество отзывов
let sortOrder = 'name_asc'; // По умолчанию сортировка по названию (А-Я)

// Функция для загрузки следующих кинотеатров
function loadMore() {
    const url = `search.php?search=${encodeURIComponent(searchTerm)}&location=${encodeURIComponent(locationFilter)}&affiliation=${encodeURIComponent(affiliationFilter)}&min_reviews=${minReviews}&offset=${offset}&ajax=1`;

    console.log('Loading more with URL:', url); // Логирование URL

    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Received data:', data); // Логирование данных
            if (data.length > 0) {
                // Добавление новых данных в список/каталог
                const catalog = document.getElementById('catalog');
                data.forEach(cinema => {
                    const photoData = JSON.parse(cinema.photo);
                    const cinemaCard = `
                        <div class="cinema-card">
                            <img src="${photoData.url}" alt="${photoData.title}" class="cinema-photo">
                            <div class="cinema-info">
                                <h3>${cinema.name}</h3>
                                <p>${cinema.location}</p>
                                <p>Отзывы: ${cinema.review_count}</p>
                            </div>
                            <button class="details-button">Подробнее</button>
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
    const url = `search.php?search=${encodeURIComponent(searchTerm)}&location=${encodeURIComponent(locationFilter)}&affiliation=${encodeURIComponent(affiliationFilter)}&min_reviews=${minReviews}&sort=${sortOrder}&offset=${offset}&ajax=1`;

    console.log('Loading data with URL:', url); // Логирование URL

    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Received data:', data); // Логирование данных
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
                            <p>Отзывы: ${cinema.review_count}</p>
                        </div>
                        <button class="details-button">Подробнее</button>
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

    console.log('Handling search with term:', searchTerm); // Логирование поискового запроса

    if (searchTerm.length >= 2 || searchTerm.length === 0) { // Поиск начинается после ввода 4 символов или при очистке
        const url = `search.php?search=${encodeURIComponent(searchTerm)}&location=${encodeURIComponent(locationFilter)}&affiliation=${encodeURIComponent(affiliationFilter)}&min_reviews=${minReviews}&ajax=1`;

        console.log('Search URL:', url); // Логирование URL

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Search results:', data); // Логирование результатов
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
                                <p>Отзывы: ${cinema.review_count}</p>
                            </div>
                            <button class="details-button">Подробнее</button>
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

    console.log('Applying filters:', {
        locationFilter,
        affiliationFilter,
        minReviews
    });

    handleSearch();
}

// Назначаем обработчики событий
document.getElementById('search-input').addEventListener('input', handleSearch);
document.getElementById('load-more-button').addEventListener('click', loadMore);
document.getElementById('filter-button').addEventListener('click', function() {
    const filters = document.getElementById('filters');
    filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
});

// Обработка меню пользователя
document.getElementById('user-menu').addEventListener('click', function(event) {
    event.preventDefault();
    const dropdown = document.getElementById('user-dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
});

// Обработчик изменения сортировки
document.getElementById('sort-select').addEventListener('change', function(event) {
    sortOrder = event.target.value; 
    offset = 0; 
    loadData();
});

// Закрываем меню при клике вне его
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('user-dropdown');
    const userMenu = document.getElementById('user-menu');
    if (dropdown.style.display === 'block' && event.target !== userMenu && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});