<?php

require_once __DIR__."/../vendor/autoload.php";

$eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
$eventDispatcher->addSubscriber(new \Quiz\Http\Listener\RouterListener());
$controllerResolver = new \Symfony\Component\HttpKernel\Controller\ControllerResolver();
$kernel = new \Symfony\Component\HttpKernel\HttpKernel($eventDispatcher, $controllerResolver);
$response = $kernel->handle(\Symfony\Component\HttpFoundation\Request::createFromGlobals());

$response->send();