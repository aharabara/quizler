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

    /**
     * @return Response
     */
    public function index(): Response
    {
        return new Response(file_get_contents(ROOT_FOLDER.'/resources/main/main.html'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function question(Request $request): JsonResponse
    {
        return new JsonResponse($this->repository->loadBy(Quiz::class, ['id' => $request->get('question')]));
    }

    /**
     * @return Response
     */
    public function answer(): Response
    {
        return new Response(file_get_contents(__DIR__.'/../../resources/main/main.html'));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function debug(Request $request): Response
    {
        return new Response("Debug: {$request->getMethod()}");
    }

    /**
     * @return Response
     */
    public function failed(): Response
    {
        return new Response('Failed request');
    }
}
