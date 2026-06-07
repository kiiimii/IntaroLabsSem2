<?php
declare(strict_types=1);

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

require dirname(__DIR__) . '/vendor/autoload.php';

$request = Request::createFromGlobals();

$session = new Session();
$session->start();
$request->setSession($session);

$loader = new YamlFileLoader(new FileLocator(dirname(__DIR__) . '/config'));
$routes = $loader->load('routes.yaml');

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($request->getPathInfo());

    foreach ($parameters as $key => $value) {
        $request->attributes->set($key, $value);
    }

    $controller = $request->attributes->get('_controller');
    [$class, $method] = explode('::', $controller, 2);

    $controllerInstance = new $class();
    $response = $controllerInstance->$method($request);

} catch (ResourceNotFoundException) {
    $response = new Response('404 Not Found', Response::HTTP_NOT_FOUND);
} catch (MethodNotAllowedException) {
    $response = new Response('405 Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
}
 
$response->send();