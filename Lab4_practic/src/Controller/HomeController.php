<?php

namespace BazaraJack\Geocoder\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends BaseController{
    public function index(Request $request): Response {
        return $this->render('home/index.html.twig',['title'=>'Геокодер']);
    }
}