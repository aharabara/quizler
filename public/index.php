<?php

use Quiz\Http\Listener\RouterListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;

require_once __DIR__ . "/../vendor/autoload.php";

$eventDispatcher = new EventDispatcher();
$eventDispatcher->addSubscriber(new RouterListener());
$controllerResolver = new ControllerResolver();
$kernel = new HttpKernel($eventDispatcher, $controllerResolver);
$response = $kernel->handle(Request::createFromGlobals());

$response->send();