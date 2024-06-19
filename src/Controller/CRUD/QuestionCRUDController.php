<?php

namespace App\Controller\CRUD;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app/quiz/{quiz}/question")]
class QuestionCRUDController extends CRUDController
{
    public function __construct(
        protected QuizRepository         $quizRepository,
        protected QuestionRepository     $questionRepository,
        protected AnswerRepository       $answerRepository,
        protected EntityManagerInterface $entityManager,
        protected Security               $security,
    ) {
        parent::__construct($this->entityManager);
    }

    #[Route("/create", name: "question_create", methods: ['POST', 'GET'], defaults: ['question' => null])]
    // #[RepresentAs(RepresentationType::FORM_SUBMITTED, redirectRoute: 'go_through_quiz', routeParams: ['quiz', 'question'])]
    // #[RepresentAs(RepresentationType::TURBO, template: '/CRUD/question/frames/_form.html.twig', turboFrame: 'form-question', cached: true)]
    // #[RepresentAs(RepresentationType::HTML, template: '/CRUD/question/form.html.twig')]
    #[Cache(maxage: 10000, public: true, mustRevalidate: false)]
    public function createQuestion(Request $request, Quiz $quiz, ?Question $question = null): Response
    {
        $question ??= (new Question())
            ->setQuiz($quiz)
            ->setAuthor($this->getUser());

        $form = $this->createForm(QuestionType::class, $question, [
            'action' => $request->getRequestUri()
        ]);

        if ($this->handleForm($form, $request)) {
            $this->addFlash('success', "Question '{$quiz->getValue()}' was updated.");

            return $this->redirect($this->generateUrl('new_grind', [
                'quiz' => $quiz,
                'question' => $question,
            ]), Response::HTTP_CREATED);
        }

        return $this->render('/CRUD/question/frames/_form.html.twig', [
            'form' => $form,
            'quiz' => $quiz,
            'question' => $question
        ]);
    }

    #[Route("/{question}/edit", name: "question_edit", methods: ['POST', 'GET'])]
    #[RepresentAs(RepresentationType::FORM_SUBMITTED, redirectRoute: 'go_through_quiz', routeParams: ['quiz', 'question'])]
    #[RepresentAs(RepresentationType::TURBO, template: '/CRUD/question/frames/_form.html.twig', turboFrame: 'form-question')]
    #[RepresentAs(RepresentationType::HTML, template: '/CRUD/question/form.html.twig')]
    public function editQuestion(Request $request, Quiz $quiz, ?int $question = null): array
    {
        $question = $this->questionRepository->findOneBy(['quiz' => $quiz->getId(), 'id' => $question]);
        if (!$question) {
            throw new NotFoundHttpException();
        }

        return $this->createQuestion($request, $quiz, $question);
    }


    #[Route("/{question}/delete", name: "question_delete", methods: ['DELETE'])]
    #[RepresentAs(RepresentationType::REDIRECT, redirectRoute: 'go_through_quiz', routeParams: ['quiz'])]
    public function deleteQuestion(Question $question): RedirectResponse
    {
        $quizId = $question->getQuiz()->getId();
        $questionId = $question->getId();

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        $this->addFlash('warning', "Question '{$question->getValue()}' with ID:{$questionId} was deleted.");

        return $this->redirect($this->generateUrl('new_grind', [
            'quiz' => $quizId
        ]));
    }

    #[Route("/list", name: "question_list", methods: ['GET'])]
    public function listQuestion(Request $request, Quiz $quiz): Response
    {
        $queryBuilder = $this->questionRepository
            ->createQueryBuilder('q')
            ->leftJoin(Answer::class, 'a', Join::WITH, 'q.id = a.question')
            ->where('q.quiz = :quizId')
            ->setParameter('quizId', $quiz->getId())
            ->orderBy('q.id', 'DESC');

        if (!$request->query->getBoolean('withAnswers')) {
            $queryBuilder->andWhere('a.value IS NULL OR a.author <> :userID')
                ->setParameter('userID', $this->getUser()->getId());
        }

        $currentQuestion = $this->questionRepository->findOneBy(['id' => $request->query->getInt('currentQuestion')]) ?? new Question();

        return $this->render('/quizzes/frames/_list-questions.html.twig', [
            'currentQuestion' => $currentQuestion,
            'questions' => $queryBuilder
                ->getQuery()
                ->getResult(),
        ]);
    }
}
