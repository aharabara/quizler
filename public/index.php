<?php

use Quiz\Http\Listener\RouterListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

require_once __DIR__."/../bootloader.php";

$eventDispatcher = new EventDispatcher();
$eventDispatcher->addSubscriber(new RouterListener());

$controllerResolver = new ControllerResolver();
$kernel = new HttpKernel($eventDispatcher, $controllerResolver);

try {
    $response = $kernel->handle(Request::createFromGlobals());
} catch (Exception $e) {
    return $e->getMessage();
}

$response->send();
