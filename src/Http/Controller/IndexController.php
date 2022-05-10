<?php

namespace Quiz\Http\Controller;

use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController
{

    private DatabaseRepository $repository;

    public function __construct()
    {
        $this->repository = new DatabaseRepository();
    }

    public function index(Request $request): Response
    {
        return new Response(file_get_contents(__DIR__ . '/../../resources/main/main.html'));
    }

    public function question(Request $request): JsonResponse
    {
        return new JsonResponse($this->repository->loadBy(Quiz::class, ['id' => $request->get('question')]));
    }

    public function answer(Request $request): Response
    {
        return new Response(file_get_contents(__DIR__ . '/../../resources/main/main.html'));
    }

    public function debug(Request $request): Response
    {
        return new Response("Debug: {$request->getMethod()}");
    }

    public function failed(): Response
    {
        return new Response('Failed request');
    }
}