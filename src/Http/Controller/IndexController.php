<?php

namespace Quiz\Http\Controller;

use Quiz\ConsoleKernel;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController
{

    private DatabaseRepository $repository;

    public function __construct(protected ConsoleKernel $kernel)
    {
        $this->repository = new DatabaseRepository($kernel->getDatabasePath());
    }

    public function index(Request $request): Response
    {
        return new Response(file_get_contents("{$this->kernel->getResourcesPath()}/main/main.html"));
    }

    public function question(Request $request): JsonResponse
    {
        return new JsonResponse($this->repository->loadBy(Quiz::class, ['id' => $request->get('question')]));
    }

    public function answer(Request $request): Response
    {
        return new Response(file_get_contents("{$this->kernel->getResourcesPath()}/main/main.html"));
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