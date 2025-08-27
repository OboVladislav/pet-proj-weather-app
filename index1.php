<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Погодное приложение</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #6dd5ed, #2193b0);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 2rem auto;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            padding: 2rem;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .search-container {
            display: flex;
            margin-bottom: 2rem;
        }
        #search-input {
            flex-grow: 1;
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px 0 0 4px;
        }
        #search-btn {
            padding: 0.8rem 1.5rem;
            background-color: #2193b0;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            font-size: 1rem;
        }
        #search-btn:hover {
            background-color: #1a7a8a;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 1rem;
        }
        .tab {
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            background-color: #f1f1f1;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background-color: #2193b0;
            color: white;
        }
        .weather-container {
            margin-top: 1rem;
        }
        .current-weather {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            background-color: rgba(240, 248, 255, 0.6);
        }
        .weather-info {
            flex: 1;
            min-width: 300px;
        }
        .temp {
            font-size: 3rem;
            font-weight: bold;
        }
        .location {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .date {
            color: #666;
        }
        .details {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: 2rem;
            gap: 1rem;
        }
        .detail-card {
            flex: 1;
            min-width: 120px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .detail-value {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }
        .forecast {
            display: flex;
            overflow-x: auto;
            gap: 1rem;
            padding: 1rem 0;
        }
        .forecast-day {
            flex: 0 0 150px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .hidden {
            display: none;
        }
        .loading {
            text-align: center;
            padding: 2rem;
        }
        .error {
            color: #d9534f;
            text-align: center;
            padding: 1rem;
            background-color: rgba(255, 220, 220, 0.5);
            border-radius: 8px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Погодное приложение</h1>
    </div>

    <div class="search-container">
        <input type="text" id="search-input" placeholder="Введите название города...">
        <button id="search-btn">Поиск</button>
    </div>

    <div class="tabs">
        <div class="tab active" data-tab="current">Текущая погода</div>
        <div class="tab" data-tab="forecast">Прогноз на 3 дня</div>
        <div class="tab" data-tab="historical">История погоды</div>
    </div>

    <div id="loading" class="loading hidden">
        <p>Загрузка данных...</p>
    </div>

    <div id="error" class="error hidden">
        <p>Произошла ошибка при загрузке данных. Пожалуйста, проверьте название города и попробуйте снова.</p>
    </div>

    <div id="current-tab" class="weather-container">
        <div class="current-weather">
            <div class="weather-info">
                <div class="location">Москва, Россия</div>
                <div class="date">Загрузка...</div>
                <div class="temp">--°C</div>
                <div>Ощущается как: --°C</div>
                <div>Состояние: --</div>
            </div>
            <div class="weather-icon">
                <img src="" alt="Weather Icon" id="weather-icon" width="100">
            </div>
        </div>

        <div class="details">
            <div class="detail-card">
                <div>Влажность</div>
                <div class="detail-value" id="humidity">--%</div>
            </div>
            <div class="detail-card">
                <div>Ветер</div>
                <div class="detail-value" id="wind">-- м/с</div>
            </div>
            <div class="detail-card">
                <div>Давление</div>
                <div class="detail-value" id="pressure">-- мм рт.ст.</div>
            </div>
            <div class="detail-card">
                <div>Видимость</div>
                <div class="detail-value" id="visibility">-- км</div>
            </div>
        </div>
    </div>

    <div id="forecast-tab" class="weather-container hidden">
        <h2>Прогноз погоды на 3 дня</h2>
        <div class="forecast" id="forecast-container">
            <!-- Forecast data will be inserted here -->
        </div>
    </div>

    <div id="historical-tab" class="weather-container hidden">
        <h2>История погоды</h2>
        <div class="historical-form">
            <label for="history-date">Выберите дату:</label>
            <input type="date" id="history-date">
            <button id="history-btn">Показать</button>
        </div>
        <div id="historical-data">
            <!-- Historical data will be inserted here -->
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Элементы интерфейса
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const tabs = document.querySelectorAll('.tab');
        const contentPanels = document.querySelectorAll('.weather-container');
        const loadingEl = document.getElementById('loading');
        const errorEl = document.getElementById('error');
        const historyDateInput = document.getElementById('history-date');
        const historyBtn = document.getElementById('history-btn');

        // Установка максимальной даты для исторических данных (сегодня)
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        historyDateInput.max = `${yyyy}-${mm}-${dd}`;

        // Обработчик поиска
        searchBtn.addEventListener('click', function() {
            const city = searchInput.value.trim();
            if (city) {
                getCurrentWeather(city);
                getForecastWeather(city);
            }
        });

        // Обработчик нажатия Enter в поле поиска
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });

        // Переключение вкладок
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');

                // Активация текущей вкладки
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Показ соответствующего содержимого
                contentPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });
                document.getElementById(`${tabId}-tab`).classList.remove('hidden');
            });
        });

        // Обработчик запроса исторических данных
        historyBtn.addEventListener('click', function() {
            const city = searchInput.value.trim();
            const date = historyDateInput.value;
            if (city && date) {
                getHistoricalWeather(city, date);
            } else {
                showError('Введите название города и выберите дату');
            }
        });

        // Функция получения текущей погоды
        function getCurrentWeather(city) {
            showLoading();
            fetch(`api.php?action=current&location=${encodeURIComponent(city)}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.error) {
                        showError(data.error.message);
                        return;
                    }
                    hideError();
                    updateCurrentWeather(data);
                })
                .catch(error => {
                    hideLoading();
                    showError('Ошибка при получении данных о погоде');
                    console.error('Error:', error);
                });
        }

        // Функция получения прогноза погоды
        function getForecastWeather(city) {
            showLoading();
            fetch(`api.php?action=forecast&location=${encodeURIComponent(city)}&days=3`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.error) {
                        showError(data.error.message);
                        return;
                    }
                    hideError();
                    updateForecast(data);
                })
                .catch(error => {
                    hideLoading();
                    showError('Ошибка при получении прогноза погоды');
                    console.error('Error:', error);
                });
        }

        // Функция получения исторических данных
        function getHistoricalWeather(city, date) {
            showLoading();
            fetch(`api.php?action=history&location=${encodeURIComponent(city)}&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.error) {
                        showError(data.error.message);
                        return;
                    }
                    hideError();
                    updateHistorical(data);
                })
                .catch(error => {
                    hideLoading();
                    showError('Ошибка при получении исторических данных');
                    console.error('Error:', error);
                });
        }

        // Обновление данных о текущей погоде на странице
        function updateCurrentWeather(data) {
            document.querySelector('.location').textContent = `${data.location.name}, ${data.location.country}`;

            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            const date = new Date(data.location.localtime);
            document.querySelector('.date').textContent = date.toLocaleDateString('ru-RU', dateOptions);

            document.querySelector('.temp').textContent = `${Math.round(data.current.temp_c)}°C`;
            document.querySelector('.weather-info div:nth-child(4)').textContent = `Ощущается как: ${Math.round(data.current.feelslike_c)}°C`;
            document.querySelector('.weather-info div:nth-child(5)').textContent = `Состояние: ${data.current.condition.text}`;

            document.getElementById('weather-icon').src = 'https:' + data.current.condition.icon;
            document.getElementById('humidity').textContent = `${data.current.humidity}%`;
            document.getElementById('wind').textContent = `${data.current.wind_kph} км/ч`;
            document.getElementById('pressure').textContent = `${Math.round(data.current.pressure_mb * 0.750062)} мм рт.ст.`;
            document.getElementById('visibility').textContent = `${data.current.vis_km} км`;
        }

        // Обновление данных о прогнозе погоды
        function updateForecast(data) {
            const forecastContainer = document.getElementById('forecast-container');
            forecastContainer.innerHTML = '';

            data.forecast.forecastday.forEach(day => {
                const date = new Date(day.date);
                const dateOptions = { weekday: 'short', month: 'short', day: 'numeric' };

                const dayElement = document.createElement('div');
                dayElement.className = 'forecast-day';
                dayElement.innerHTML = `
                        <div>${date.toLocaleDateString('ru-RU', dateOptions)}</div>
                        <img src="https:${day.day.condition.icon}" alt="${day.day.condition.text}">
                        <div>${day.day.condition.text}</div>
                        <div>${Math.round(day.day.maxtemp_c)}°C / ${Math.round(day.day.mintemp_c)}°C</div>
                        <div>Осадки: ${day.day.totalprecip_mm} мм</div>
                        <div>Влажность: ${day.day.avghumidity}%</div>
                    `;
                forecastContainer.appendChild(dayElement);
            });
        }

        // Обновление исторических данных
        function updateHistorical(data) {
            const historicalData = document.getElementById('historical-data');
            historicalData.innerHTML = '';

            const date = new Date(data.forecast.forecastday[0].date);
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

            const historyElement = document.createElement('div');
            historyElement.className = 'current-weather';
            historyElement.innerHTML = `
                    <div class="weather-info">
                        <div class="location">${data.location.name}, ${data.location.country}</div>
                        <div class="date">${date.toLocaleDateString('ru-RU', dateOptions)}</div>
                        <div class="temp">Макс: ${Math.round(data.forecast.forecastday[0].day.maxtemp_c)}°C / Мин: ${Math.round(data.forecast.forecastday[0].day.mintemp_c)}°C</div>
                        <div>Средняя температура: ${Math.round(data.forecast.forecastday[0].day.avgtemp_c)}°C</div>
                        <div>Состояние: ${data.forecast.forecastday[0].day.condition.text}</div>
                    </div>
                    <div class="weather-icon">
                        <img src="https:${data.forecast.forecastday[0].day.condition.icon}" alt="Weather Icon" width="100">
                    </div>
                `;

            const detailsElement = document.createElement('div');
            detailsElement.className = 'details';
            detailsElement.innerHTML = `
                    <div class="detail-card">
                        <div>Осадки</div>
                        <div class="detail-value">${data.forecast.forecastday[0].day.totalprecip_mm} мм</div>
                    </div>
                    <div class="detail-card">
                        <div>Макс. ветер</div>
                        <div class="detail-value">${data.forecast.forecastday[0].day.maxwind_kph} км/ч</div>
                    </div>
                    <div class="detail-card">
                        <div>Ср. влажность</div>
                        <div class="detail-value">${data.forecast.forecastday[0].day.avghumidity}%</div>
                    </div>
                    <div class="detail-card">
                        <div>УФ индекс</div>
                        <div class="detail-value">${data.forecast.forecastday[0].day.uv}</div>
                    </div>
                `;

            historicalData.appendChild(historyElement);
            historicalData.appendChild(detailsElement);

            // Добавление почасовых данных, если они доступны
            if (data.forecast.forecastday[0].hour && data.forecast.forecastday[0].hour.length > 0) {
                const hourlyTitle = document.createElement('h3');
                hourlyTitle.textContent = 'Почасовые данные';
                historicalData.appendChild(hourlyTitle);

                const hourlyContainer = document.createElement('div');
                hourlyContainer.className = 'forecast';

                // Отображаем данные через каждые 3 часа для компактности
                for (let i = 0; i < data.forecast.forecastday[0].hour.length; i += 3) {
                    const hour = data.forecast.forecastday[0].hour[i];
                    const hourTime = new Date(hour.time);

                    const hourElement = document.createElement('div');
                    hourElement.className = 'forecast-day';
                    hourElement.innerHTML = `
                            <div>${hourTime.getHours()}:00</div>
                            <img src="https:${hour.condition.icon}" alt="${hour.condition.text}" width="50">
                            <div>${Math.round(hour.temp_c)}°C</div>
                            <div>${hour.condition.text}</div>
                            <div>Влажность: ${hour.humidity}%</div>
                            <div>Ветер: ${hour.wind_kph} км/ч</div>
                        `;
                    hourlyContainer.appendChild(hourElement);
                }

                historicalData.appendChild(hourlyContainer);
            }
        }

        // Вспомогательные функции
        function showLoading() {
            loadingEl.classList.remove('hidden');
        }

        function hideLoading() {
            loadingEl.classList.add('hidden');
        }

        function showError(message) {
            errorEl.querySelector('p').textContent = message;
            errorEl.classList.remove('hidden');
        }

        function hideError() {
            errorEl.classList.add('hidden');
        }

        // Загрузка данных для Москвы по умолчанию при открытии страницы
        searchInput.value = 'Москва';
        getCurrentWeather('Москва');
        getForecastWeather('Москва');
    });
</script>
</body>
</html>