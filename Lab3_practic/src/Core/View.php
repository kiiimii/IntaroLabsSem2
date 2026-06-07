<?php

namespace BazaraJack\Library\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View {
    private static $twig = null;

    public static function getTwig(): Environment {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/../View');
            self::$twig = new Environment($loader, [
                'cache' => false,
                'autoescape' => 'html',
            ]);
        }
        return self::$twig;
    }
}