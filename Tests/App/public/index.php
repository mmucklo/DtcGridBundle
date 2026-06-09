<?php

use Dtc\GridBundle\Tests\App\Kernel;

require_once __DIR__.'/../../../vendor/autoload.php';

// Let PHP built-in server handle static files directly
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ('/' !== $uri && file_exists(__DIR__.$uri)) {
    return false;
}

$kernel = new Kernel('test', true);
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
