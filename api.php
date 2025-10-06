<?php
require_once __DIR__ . '/vendor/autoload.php';

use back\WeatherBackend;

$weatherBackend = new WeatherBackend();
///
// Проверка наличия параметра действия
if (!isset($_GET['action'])) {
    sendResponse(['error' => ['code' => 400, 'message' => 'Параметр action не указан']]);
    exit;
}

// Проверка наличия параметра местоположения
if (!isset($_GET['location'])) {
    sendResponse(['error' => ['code' => 400, 'message' => 'Параметр location не указан']]);
    exit;
}

$action = $_GET['action'];
$location = $_GET['location'];

// Выбор действия в зависимости от параметра action
switch ($action) {
    case 'current':
        // Получение текущей погоды
        $result = $weatherBackend->getCurrentWeather($location);
        break;

    case 'forecast':
        // Получение прогноза погоды
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 3;
        $result = $weatherBackend->getForecastWeather($location, $days);
        break;

    case 'history':
        // Получение исторических данных о погоде
        if (!isset($_GET['date'])) {
            sendResponse(['error' => ['code' => 400, 'message' => 'Параметр date не указан для исторических данных']]);
            exit;
        }
        $date = $_GET['date'];
        $result = $weatherBackend->getHistoricalWeather($location, $date);
        break;

    case 'search':
        // Поиск местоположений
        $result = $weatherBackend->searchLocation($location);
        break;

    default:
        sendResponse(['error' => ['code' => 400, 'message' => 'Неизвестное действие']]);
        exit;
}

// Отправка ответа клиенту

if ($result) {
    sendResponse($result);
} else {
    sendResponse('Ошибка!');
}

/**
 * Отправляет данные в формате JSON с соответствующими заголовками
 *
 * @param mixed $data Данные для отправки
 */
function sendResponse($data) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data);
}
?>
