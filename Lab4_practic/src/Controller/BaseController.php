<?php

namespace BazaraJack\Geocoder\Controller;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class BaseController{ 
    private ?Environment $twig = null;

    protected function getTwig():Environment {
        if($this->twig === null){
            $loader = new FilesystemLoader(__DIR__ . '/../../templates');
            $this->twig = new Environment($loader,['cache'=>false]);  
        }

        return $this->twig;
    }

    protected function render(string $template, array $params = []): Response {
        $content = $this->getTwig()->render($template,$params);

        return new Response($content);
    }
}

