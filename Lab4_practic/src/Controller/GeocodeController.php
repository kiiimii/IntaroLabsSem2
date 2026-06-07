<?php

namespace BazaraJack\Geocoder\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use BazaraJack\Geocoder\Model\GeocoderService;

class GeocodeController
{
    public function search(Request $request): JsonResponse
    {
        // 1. Получаем строку адреса из AJAX-запроса
        // query->get() ищет в $_GET, request->get() ищет в $_POST (поддерживает оба варианта)
        $address = $request->request->get('address') ?? $request->query->get('address');
        
        // Дополнительно проверяем, если фронтенд прислал данные в формате JSON-body
        if (!$address && $request->getContentTypeFormat() === 'json') {
            $data = json_decode($request->getContent(), true);
            $address = $data['address'] ?? null;
        }

        // Очищаем адрес от лишних пробелов по краям
        $address = $address ? trim($address) : null;

        if (!$address) {
            return new JsonResponse(['error' => 'Параметр "address" обязателен для заполнения.'], 400);
        }

        try {
            // 2. Получаем API-ключ из переменных окружения (в Docker-compose мы его прописали)
            $apiKey = getenv('YANDEX_API_KEY');

            // 3. Инициализируем наш сервис-модель и передаем туда адрес
            $geocoder = new GeocoderService($apiKey);
            $result = $geocoder->decode($address);

            // 4. Возвращаем успешный ответ со статусом 200
            // JsonResponse автоматически устанавливает заголовок Content-Type: application/json
            return new JsonResponse($result, 200);

        } catch (\Throwable $e) {
            // Если модель выбросила RuntimeException (сеть упала, адрес не найден и т.д.)
            // перехватываем ошибку и возвращаем понятный JSON со статусом 500 или 404
            $statusCode = str_contains($e->getMessage(), 'не найден') ? 404 : 500;
            
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $statusCode);
        }
    }
}