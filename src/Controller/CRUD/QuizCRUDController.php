<?php

namespace App\Controller\CRUD;

use App\Entity\Quiz;
use App\Form\QuizType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            'attr' => [
                'data-turbo-action' => 'advance'
            ]
        ]);

        if ($this->handleForm($form, $request)) {
            if (!$request->attributes->get('_route') === 'quiz_create') {
                $this->addFlash('success', "Quiz '{$quiz->getValue()}' was created.");
            } else {
                $this->addFlash('success', "Quiz '{$quiz->getValue()}' was updated.");
            }

            return $this->redirectToRoute('go_through_quiz', ['quiz' => $quiz->getId()]);
        }

        return $this->render(
            'CRUD/quiz/form.html.twig',
            [
                'form' => $form,
                'quiz' => $quiz
            ]);
    }

    #[Route("/list", name: "quiz_list", methods: ['GET'])]
    public function listQuizzes(Request $request): Response
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

        return $this->render(
            'CRUD/quiz/list.html.twig',
            [
                'list' => $paginator->getIterator(),
                'totalPages' => ceil($paginator->count() / $perPage),
                'perPage' => $perPage,
                'page' => $page,
            ]);
    }

    #[Route("/{quiz}/delete", name: "quiz_delete", methods: ['DELETE'])]
    public function deleteQuiz(Quiz $quiz): Response
    {
        $id = $quiz->getId();

        $this->entityManager->remove($quiz);
        $this->entityManager->flush();

        $this->addFlash('success', "Quiz '{$quiz->getValue()}' with ID:{$id} was deleted.");

        return $this->redirectToRoute('quiz_list', status: Response::HTTP_SEE_OTHER);
    }
}
