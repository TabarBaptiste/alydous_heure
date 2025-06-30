<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    #[Route('/api/debug-jwt', name: 'debug_jwt')]
    public function __invoke(): Response
    {
        $privatePath = $_ENV['JWT_SECRET_KEY'];
        $exists = file_exists($privatePath) ? 'OK' : 'NOT FOUND';
        $readable = is_readable($privatePath) ? 'OK' : 'NOT READABLE';
        $perms = substr(sprintf('%o', fileperms($privatePath)), -4);

        return new Response("JWT Secret Path: $privatePath \n<br>Exists: $exists \n<br>Readable: $readable \n<br>Permissions: $perms");

    }
}