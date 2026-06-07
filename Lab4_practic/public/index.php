<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require dirname(__DIR__) . '/vendor/autoload.php';

$routes = require __DIR__ . '/../config/routes.php';

$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes,$context);

try{
    $attributes = $matcher->match($request->getPathInfo());
    $request->attributes->add($attributes);

    [$controllerClass,$method] = $request->attributes->get('_controller');
    $controller = new $controllerClass();

    /** @var Response $response */
    $response = $controller->$method($request);
    
}catch(ResourceNotFoundException $e){
    $response = new JsonResponse(['error' => 'Страница не найдена'], 404);
}catch(\Throwable $e){
    $response = new JsonResponse(['error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()], 500);
}

$response->send();



