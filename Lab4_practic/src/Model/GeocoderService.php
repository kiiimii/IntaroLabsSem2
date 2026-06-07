<?php

namespace BazaraJack\Geocoder\Model;

use RuntimeException;

class GeocoderService
{
    private const API_URL = 'https://geocode-maps.yandex.ru/1.x/';

    public function __construct(
        private string $apiKey
    ) {
        if (empty($this->apiKey)) {
            throw new RuntimeException('API-ключ Яндекс.Геокодера не задан.');
        }
    }


    public function decode(string $address): array
    {
        // 1. Первый запрос: получаем координаты и нормализованный адрес
        $firstResponse = $this->sendRequest([
            'geocode' => $address,
            'format' => 'json',
        ]);

        $geoObject = $firstResponse['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'] ?? null;

        if (!$geoObject) {
            throw new RuntimeException('Адрес не найден или указан неверно.');
        }

        // Вытаскиваем красивый структурированный адрес
        $structuredAddress = $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? $address;

        // Вытаскиваем координаты. Яндекс возвращает строку вида "Долгота Широта" (например: "39.537 52.610")
        $posString = $geoObject['Point']['pos'] ?? null;
        if (!$posString) {
            throw new RuntimeException('Не удалось определить координаты для указанного адреса.');
        }

        [$longitude, $latitude] = explode(' ', $posString);

        // 2. Второй запрос: ищем метро по полученным координатам
        // В геокод передаем координаты строго через запятую БЕЗ пробелов: "Долгота,Широта"
        $metroResponse = $this->sendRequest([
            'geocode' => sprintf('%s,%s', $longitude, $latitude),
            'kind' => 'metro',
            'results' => 1,
            'format' => 'json',
        ]);

        $metroObject = $metroResponse['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'] ?? null;
        
        // Если метро нашлось — берем его имя, иначе возвращаем понятный текст
        $metroName = $metroObject ? ($metroObject['name'] ?? 'Неизвестная станция') : 'Ближайшего метро нет';

        // Возвращаем результат. Для вывода пользователю переворачиваем координаты в формат "Широта, Долгота"
        return [
            'address'     => $structuredAddress,
            'coordinates' => sprintf('%s, %s', $latitude, $longitude),
            'metro'       => $metroName,
        ];
    }

    /**
     * Универсальный метод для отправки cURL-запросов к API Яндекса
     */
    private function sendRequest(array $params): array
    {
        // Автоматически подставляем API-ключ к любым параметрам
        $params['apikey'] = $this->apiKey;

        // Строим полный URL с экранированием параметров (urlencode под капотом)
        $url = self::API_URL . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Таймаут 5 секунд, чтобы скрипт не зависал
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Ошибка сети при запросе к Яндекс.Картам: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new RuntimeException('Яндекс.Карты вернули ошибку HTTP: ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Не удалось распарсить JSON-ответ от Яндекса.');
        }

        return $data;
    }
}