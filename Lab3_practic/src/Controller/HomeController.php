<?php

declare(strict_types=1);

namespace BazaraJack\Library\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HomeController
{
    public function index(Request $request): Response
    {
        return new RedirectResponse('/books');
    }
}