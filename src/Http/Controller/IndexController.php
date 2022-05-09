<?php

namespace Quiz\Http\Controller;

use Quiz\ORM\Repository\QuizRepository;
use Quiz\ORM\StorageDriver\DBStorageDriver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController
{
    private QuizRepository $quizRepository;

    public function __construct()
    {
        $this->quizRepository = new QuizRepository(new DBStorageDriver());
    }

    public function index(Request $request): Response
    {
        return new Response(file_get_contents(__DIR__.'/../../resources/main/main.html'));
    }

    public function question(Request $request): JsonResponse
    {
        return new JsonResponse([
            'question' => 'My question?'
        ]);
    }

    public function answer(Request $request): Response
    {
        return new Response(file_get_contents(__DIR__.'/../../resources/main/main.html'));
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