<?php

namespace App\Controller\CRUD;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app/quiz")]
class QuizCRUDController extends CRUDController
{
    public function __construct(
        protected QuizRepository         $quizRepository,
        protected QuestionRepository     $questionRepository,
        protected AnswerRepository       $answerRepository,
        protected EntityManagerInterface $entityManager,
        protected Security               $security,
    )
    {
        parent::__construct($this->entityManager);
    }

    #[Route("/create", name: "quiz_create", methods: ['POST', 'GET'])]
    #[Route("/{quiz}/edit", name: "quiz_edit", methods: ['POST', 'GET'])]
    public function createQuiz(Request $request, Quiz $quiz = null): Response
    {
        $quiz ??= (new Quiz())
            ->setVersion(1);

        $form = $this->createForm(QuizType::class, $quiz, [
            'action' => $request->getRequestUri(),
            'attr' => [
                'data-turbo-action' => 'advance'
            ]
        ]);

        if ($this->handleForm($form, $request)) {
            if (!$this->currentRouteIs($request, 'quiz_create')) {
                $this->addFlash('success', "Quiz '{$quiz->getValue()}' was created.");
            } else {
                $this->addFlash('success', "Quiz '{$quiz->getValue()}' was updated.");
            }

            return $this->redirectToRoute('quiz_list');
        }

        return $this->render(
            'CRUD/quiz/form.html.twig',
            [
                'form' => $form,
                'quiz' => $quiz
            ]);
    }

    #[Route("/list", name: "quiz_list", methods: ['GET'])]
    #[RepresentAs(RepresentationType::TURBO, template: '/CRUD/quiz/frame/_list-quiz.html.twig' ,turboFrame: 'list-quiz', cached: true)]
    #[RepresentAs(RepresentationType::TURBO, template: '/CRUD/quiz/list.html.twig', turboFrame: 'page')]
    #[RepresentAs(RepresentationType::HTML, template: '/CRUD/quiz/list.html.twig')]
    #[Cache(maxage: 10000, public: true, mustRevalidate: false)]
    public function listQuizzes(Request $request): array
    {
        $perPage = max($request->query->getInt('perPage', 1), 10);
        $page = max($request->query->getInt('page', 1), 1);
        $search = $request->query->get('search');

        $queryBuilder = $this
            ->quizRepository
            ->createQueryBuilder('quiz')
            ->orderBy('quiz.id', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage);

        if (!empty($search)) {
            $queryBuilder
                ->where('quiz.value LIKE :search')
                ->setParameter('search', "%{$search}%");
        }

        $paginator = new Paginator($queryBuilder);

        return [
            'list' => $paginator->getIterator(),
            'totalPages' => ceil($paginator->count() / $perPage),
            'perPage' => $perPage,
            'page' => $page,
        ];
    }

    #[Route("/{quiz}/delete", name: "quiz_delete", methods: ['DELETE'])]
    #[RepresentAs(RepresentationType::REDIRECT, redirectRoute: 'quiz_list', routeParams: ['quiz'])]
    public function deleteQuiz(Quiz $quiz): array
    {
        $id = $quiz->getId();

        $this->entityManager->remove($quiz);
        $this->entityManager->flush();

        $this->addFlash('success', "Quiz '{$quiz->getValue()}' with ID:{$id} was deleted.");

        return [
            'quiz' => $id
        ];
    }
}
