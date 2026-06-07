<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use BazaraJack\Geocoder\Controller\HomeController;
use BazaraJack\Geocoder\Controller\GeocodeController;

$routes = new RouteCollection();

$routes->add('home', new Route('/',[
    '_controller'=> [HomeController::class,'index']
],[],[],'',[],['GET']));

$routes->add('api_geocode', new Route('/api/geocode', [
    '_controller' => [GeocodeController::class, 'search']
], [], [], '', [], ['POST']));


return $routes;