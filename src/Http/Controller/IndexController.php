<?php

namespace Quiz\Http\Controller;

use Quiz\ConsoleKernel;
use Quiz\Domain\Answer;
use Quiz\Domain\Question;
use Quiz\Domain\Quiz;
use Quiz\ORM\Repository\DatabaseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class IndexController
{

    private DatabaseRepository $repository;
    private Serializer $serializer;
    private string $resourcesPath;

    public function __construct()
    {
        /*fixme extract */
        $databasePath = getenv('HOME') . "/.quizzler/storage/quizzler.db";
        $this->resourcesPath = __DIR__ . '/../../../resources';

        $this->serializer = new Serializer([
            new DateTimeNormalizer(),
            new ObjectNormalizer(new ClassMetadataFactory(new AnnotationLoader())),
        ]);
        $this->repository = new DatabaseRepository($databasePath);

    }

    public function index(Request $request): Response
    {
        return new Response(file_get_contents("{$this->resourcesPath}/main/main.html"));
    }

    public function quiz(Request $request): JsonResponse
    {
        $quiz = $this->repository->loadBy(Quiz::class, ['id' => $request->get('qid')]);

        return new JsonResponse($this->serializer->normalize($quiz, null,  ['groups' => ['api']]));
    }

    public function quizzes(Request $request): JsonResponse
    {
        return new JsonResponse($this->repository->getStats());
    }

    public function answer(Request $request): JsonResponse
    {
        $response = json_decode($request->getContent(), true);
        /** @var Question $question */
        $question = $this
            ->repository
            ->loadBy(Question::class, ['id' => $request->query->get('question_id')]);

        $answer = (new Answer())
            ->setQuestion($question)
            ->setContent($response['content'])
            ->setIsCorrect(true);
        $answer->setUpdatedAt(date_create());

        $this->repository->save($answer);

        return new JsonResponse($this->serializer->normalize($answer, null,  ['groups' => ['api']]));
    }

    public function debug(Request $request): Response
    {
        return new Response("<pre> Debug: {$request->getMethod()}\n".var_export($request, true));
    }

    public function failed(): Response
    {
        return new Response('Failed request');
    }
}