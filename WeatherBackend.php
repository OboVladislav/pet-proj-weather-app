<?php

namespace back;

/**
 * Класс для работы с API погоды (WeatherAPI)
 */
class WeatherBackend {
    // API ключ для WeatherAPI
    private $apiKey = '06f75f7cc5c84ed49c0212656250304'; // Замените на свой API ключ

    // Базовый URL для API
    private $baseUrl = 'http://api.weatherapi.com/v1';

    // Директория для кэширования результатов
    private $cacheDir = '';

    // Время жизни кэша по умолчанию (3600 секунд = 1 час)
    private $defaultCacheTtl = 3600;

    /**
     * Конструктор класса
     */
    public function __construct() {
        // Установка директории для кэша
        $this->cacheDir = __DIR__ . '/cache';

        // Создание директории для кэша, если она не существует
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        // Проверка наличия API ключа
        if ($this->apiKey === '06f75f7cc5c84ed49c0212656250304') {
            error_log('WeatherAPI key not set. Please replace with your actual API key.');
        }
    }

    /**
     * Получение текущей погоды для указанного местоположения
     *
     * @param string $location Название города или координаты
     * @return array Данные о текущей погоде
     */
    public function getCurrentWeather($location) {
        $endpoint = '/current.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $location,
            'aqi' => 'no',
            'lang' => 'ru'
        ];

        // Используем короткий кэш для текущей погоды (30 минут)
        $cacheKey = 'current_' . md5($location);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 1800);
    }

    /**
     * Получение прогноза погоды для указанного местоположения
     *
     * @param string $location Название города или координаты
     * @param int $days Количество дней прогноза (до 10)
     * @return array Данные о прогнозе погоды
     */
    public function getForecastWeather($location, $days = 3) {
        $endpoint = '/forecast.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $location,
            'days' => min($days, 10),
            'aqi' => 'no',
            'alerts' => 'yes',
            'lang' => 'ru'
        ];

        // Кэш на 3 часа для прогнозов
        $cacheKey = 'forecast_' . md5($location . '_' . $days);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 10800);
    }

    /**
     * Получение исторических данных о погоде
     *
     * @param string $location Название города или координаты
     * @param string $date Дата в формате YYYY-MM-DD
     * @return array Исторические данные о погоде
     */
    public function getHistoricalWeather($location, $date) {
        $endpoint = '/history.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $location,
            'dt' => $date,
            'lang' => 'ru'
        ];

        // Исторические данные можно кэшировать надолго (неделя)
        $cacheKey = 'history_' . md5($location . '_' . $date);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 604800);
    }

    /**
     * Поиск местоположений по запросу
     *
     * @param string $query Поисковый запрос
     * @return array Результаты поиска
     */
    public function searchLocation($query) {
        $endpoint = '/search.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $query,
            'lang' => 'ru'
        ];

        // Кэшируем результаты поиска на день
        $cacheKey = 'search_' . md5($query);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 86400);
    }

    /**
     * Получение данных с использованием кэширования
     *
     * @param string $endpoint Конечная точка API
     * @param array $params Параметры запроса
     * @param string $cacheKey Ключ кэша
     * @param int $cacheTtl Время жизни кэша
     * @return array Результат запроса
     */
    private function getDataWithCache($endpoint, $params, $cacheKey, $cacheTtl) {
        // Проверяем наличие данных в кэше
        $cachedData = $this->getFromCache($cacheKey);

        // Если есть валидные данные в кэше, возвращаем их
        if ($cachedData !== false) {
            return $cachedData;
        }

        // Иначе делаем запрос к API
        $result = $this->makeRequest($endpoint, $params);

        // Если запрос успешен, кэшируем результат
        if (!isset($result['error'])) {
            $this->saveToCache($cacheKey, $result, $cacheTtl);
        }

        return $result;
    }

    /**
     * Выполняет HTTP-запрос к API с использованием cURL
     *
     * @param string $endpoint Конечная точка API
     * @param array $params Параметры запроса
     * @return array Результат запроса
     */
    private function makeRequest($endpoint, $params) {
        // Формирование URL с параметрами
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);

        // Инициализация cURL сессии
        $curl = curl_init();

        // Настройка опций cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: WeatherApp/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => false, // В продакшн включить проверку SSL
        ]);

        // Выполнение запроса
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // Логирование запроса в файл


        // Закрытие cURL сессии
        curl_close($curl);

        // Обработка ошибок
        if ($err) {
            return [
                'error' => [
                    'code' => 500,
                    'message' => 'Ошибка cURL: ' . $err
                ]
            ];
        }

        // Преобразование ответа из JSON
        $decoded = json_decode($response, true);

        // Если не удалось декодировать JSON
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => [
                    'code' => 500,
                    'message' => 'Ошибка декодирования JSON: ' . json_last_error_msg()
                ]
            ];
        }

        // Проверка успешности запроса
        if ($httpCode !== 200) {
            return [
                'error' => [
                    'code' => $httpCode,
                    'message' => isset($decoded['error']['message'])
                        ? $decoded['error']['message']
                        : 'Неизвестная ошибка API (код ' . $httpCode . ')'
                ]
            ];
        }

        // Возврат данных
        return $decoded;
    }

    /**
     * Сохранение данных в кэш
     *
     * @param string $key Ключ кэша
     * @param mixed $data Данные для кэширования
     * @param int $ttl Время жизни кэша в секундах
     * @return bool Результат сохранения
     */
    private function saveToCache($key, $data, $ttl = null) {
        if ($ttl === null) {
            $ttl = $this->defaultCacheTtl;
        }

        $cacheFile = $this->cacheDir . '/' . $key . '.json';

        // Сохранение данных в кэш
        $cacheData = [
            'expires' => time() + $ttl,
            'data' => $data
        ];

        return file_put_contents($cacheFile, json_encode($cacheData)) !== false;
    }

    /**
     * Получение данных из кэша
     *
     * @param string $key Ключ кэша
     * @return mixed Данные из кэша или false, если кэш недействителен
     */
    private function getFromCache($key) {
        $cacheFile = $this->cacheDir . '/' . $key . '.json';

        // Если файл кэша не существует
        if (!file_exists($cacheFile)) {
            return false;
        }

        // Чтение данных из кэша
        $cacheData = json_decode(file_get_contents($cacheFile), true);

        // Проверка валидности формата данных
        if (!isset($cacheData['expires']) || !isset($cacheData['data'])) {
            return false;
        }

        // Проверка срока действия кэша
        if ($cacheData['expires'] < time()) {
            // Кэш устарел, удаляем его
            unlink($cacheFile);
            return false;
        }

        // Возврат данных из кэша
        return $cacheData['data'];
    }

    /**
     * Очистка устаревших файлов кэша
     *
     * @return int Количество удаленных файлов
     */
    public function cleanExpiredCache() {
        $count = 0;
        $files = glob($this->cacheDir . '/*.json');

        foreach ($files as $file) {
            $cacheData = json_decode(file_get_contents($file), true);

            if (isset($cacheData['expires']) && $cacheData['expires'] < time()) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Логирование API запросов
     *
     * @param string $url URL запроса
     * @param int $httpCode HTTP код ответа
     * @param string $error Ошибка, если есть
     */
    private function logApiRequest($url, $httpCode, $error) {
        $logDir = __DIR__ . '/logs';

        // Создание директории для логов, если она не существует
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/api_requests.log';

        // Формирование строки лога
        $logEntry = date('Y-m-d H:i:s') . ' | ' .
            $_SERVER['REMOTE_ADDR'] . ' | ' .
            $url . ' | ' .
            $httpCode . ' | ' .
            ($error ? 'ERROR: ' . $error : 'OK') . PHP_EOL;

        // Запись в лог-файл
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Форматирование данных о погоде для отображения
     *
     * @param array $data Данные погоды
     * @param string $format Формат вывода (json, html)
     * @return mixed Отформатированные данные
     */
    public function formatWeatherData($data, $format = 'json') {
        // Этот метод можно расширить для различных форматов вывода
        if ($format === 'html') {
            // Формирование HTML для вывода данных о погоде
            // Реализация по необходимости
            return '<div class="weather-data">...</div>';
        }

        // По умолчанию возвращаем JSON
        return $data;
    }

    /**
     * Получение данных о качестве воздуха
     *
     * @param string $location Местоположение
     * @return array Данные о качестве воздуха
     */
    public function getAirQuality($location) {
        $endpoint = '/current.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $location,
            'aqi' => 'yes',
            'lang' => 'ru'
        ];

        $cacheKey = 'air_quality_' . md5($location);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 3600);
    }

    /**
     * Получение астрономических данных (восход/закат и т.д.)
     *
     * @param string $location Местоположение
     * @param string $date Дата в формате YYYY-MM-DD
     * @return array Астрономические данные
     */
    public function getAstronomy($location, $date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        $endpoint = '/astronomy.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $location,
            'dt' => $date,
            'lang' => 'ru'
        ];

        $cacheKey = 'astronomy_' . md5($location . '_' . $date);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 86400); // Кэш на день
    }

    /**
     * Получение IP-адреса пользователя и определение его местоположения
     *
     * @return array Данные о местоположении
     */
    public function getLocationByIp() {
        $ip = $_SERVER['REMOTE_ADDR'];

        $endpoint = '/ip.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $ip,
            'lang' => 'ru'
        ];

        $cacheKey = 'ip_location_' . md5($ip);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 86400); // Кэш на день
    }

    /**
     * Получение спортивных событий с учетом погоды
     *
     * @param string $location Местоположение
     * @return array Данные о спортивных событиях
     */
    public function getSportsEvents($location) {
        $endpoint = '/sports.json';
        $params = [
            'key' => $this->apiKey,
            'q' => $location,
            'lang' => 'ru'
        ];

        $cacheKey = 'sports_' . md5($location);
        return $this->getDataWithCache($endpoint, $params, $cacheKey, 21600); // Кэш на 6 часов
    }
}
?>