<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/app")]
class HomeController extends AbstractController
{
    public function __construct(
        protected QuizRepository         $quizRepository,
        protected QuestionRepository     $questionRepository,
        protected AnswerRepository       $answerRepository,
        protected EntityManagerInterface $entityManager,
        protected Security               $security,
    )
    {
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('home/home.html.twig', []);
    }

    /** @todo refactor the routes. */
    #[Route('/quizzler/{quiz}', name: 'app_quiz', defaults: ['quiz' => null])]
    public function index(Request $request, ?Quiz $quiz): Response
    {
        $questionId = $request->query->getInt('question');
        $answerId = $request->query->getInt('answer');
        $previousQuestionId = $request->query->getInt('previousQuestion');

        $previousQuestion = $previousQuestionId
            ? $this->getQuizQuestionById($quiz, $previousQuestionId)
            : new Question() /* as null object*/
        ;

        $quiz ??= $this->getFirstQuiz();
        if ($answerId !== 0) {
            $answer = $this->answerRepository->find($answerId);
            $question = $answer->getQuestion();
        } else {
            $answer = new Answer();
            $question = $this->getQuizQuestionById($quiz, $questionId)
                ?? $this->getFirstUnansweredQuizQuestion($quiz)
                ?? $this->getLastQuizQuestion($quiz);
        }

        return $this->render('quizzes/quizzes.html.twig', [
            'currentQuiz' => $quiz,
            'previousQuestion' => $previousQuestion,
            'currentQuestion' => $question,
            'currentAnswer' => $answer
        ]);
    }


    #[Route(
        path: '/quizzler/{quiz}/question/{question}/answer/{answer}',
        name: 'app_quiz_question_answer',
        defaults: ['answer' => null],
        methods: ['POST']
    )]
    public function answer(Request $request, Question $question, ?Answer $answer, ValidatorInterface $validator): Response
    {
        if (empty($answer)) {
            $answer = (new Answer())
                ->setAuthor($this->security->getUser())
                ->setQuestion($question)
                ->setCorrect(true)
                ->setCreatedAt(new \DateTimeImmutable());
        }

        $answer = $answer->setValue($request->request->get('answer'));

        $violations = $validator->validate($answer);
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $violation->getMessage());
            }
            return new RedirectResponse(
                $this->generateUrl('app_quiz', [
                    'quiz' => $request->attributes->get('quiz'),
                    'question' => $request->attributes->get('question'),
                    'answer' => $answer->getId()
                ]),
                Response::HTTP_SEE_OTHER
            );

        }
        $this->addFlash('success', 'Answer added.');
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        return new RedirectResponse(
            $this->generateUrl('app_quiz', [
                'quiz' => $request->attributes->get('quiz'),
                'previousQuestion' => $request->attributes->get('question'),
            ]),
            Response::HTTP_SEE_OTHER
        );
    }


    #[Route('/quizzes/{quiz}/list', name: 'app_quiz_list')]
    public function quizzes(Request $request, ?Quiz $quiz): Response
    {
        $quiz ??= $this->getFirstQuiz();
        $session = $request->getSession();

        $page = max($request->query->get('quizListPage', $session->get('app.quiz-list.page', 1)), 1);

        $session->set('app.quiz-list.page', $page);

        $perPage = 10;

        $queryBuilder = $this->quizRepository
            ->createQueryBuilder('q')
            ->setMaxResults($perPage)
            ->orderBy('q.answered', 'DESC')
            ->setFirstResult(($page - 1) * $perPage);

        $paginator = new Paginator($queryBuilder);

        return $this->render('quizzes/frames/_list-quizzes.html.twig', [
            'quizzes' => $paginator->getIterator()->getArrayCopy(),
            'currentQuiz' => $quiz,
            'hasNextPage' => ($paginator->count() - $perPage * $page) > 0,
            'hasPreviousPage' => $page > 1,
            'page' => $page
        ]);
    }

    #[Route('/quizzler/answers/{answer}/', name: 'app_quiz_delete', methods: ['DELETE'])]
    public function deleteAnswer(Request $request, ?Answer $answer): Response
    {
        $this->entityManager->remove($answer);
        $this->entityManager->flush($answer);

        return $this->forward(
            'App\Controller\HomeController::questions',
            path: [
                'quiz' => $answer->getQuestion()->getQuiz()->getId(),
            ],
            query: [
                'question' => $answer->getQuestion()->getId(),
                'questionsPage' => $request->request->getInt('questionsPage')
            ]);
    }

    #[Route('/quizzler/answers/{answer}/', name: 'app_quiz_answer_toggle', methods: ['POST'])]
    public function toggleAnswerValidity(Request $request, ?Answer $answer): Response
    {
        $answer->setCorrect(!$answer->isCorrect());

        $this->entityManager->persist($answer);
        $this->entityManager->flush($answer);

        return new RedirectResponse(
            $this->generateUrl('app_quiz_questions', [
                'quiz' => $request->request->get('quiz'),
                'question' => $request->request->get('question'),
                'questionsPage' => $request->request->get('questionsPage'),
            ]),
            Response::HTTP_SEE_OTHER
        );
    }

    #[Route('/quizzler/{quiz}/question', name: 'app_quiz_questions')]
    public function questions(Request $request, Quiz $quiz): Response
    {
        /*@todo extract paginator logic to a separate class*/
        $queryBuilder = $this->questionRepository->createQueryBuilder('q');
        $page = max(1, $request->query->getInt('questionsPage', 1));
        $perPage = 20;

        // @todo extract to repository
        $queryBuilder
            ->leftJoin(Answer::class, 'a', Join::WITH, 'q.id = a.question')
            ->where('q.quiz = :quiz')
            ->andWhere('a.id IS NOT NULL')
            ->setParameter('quiz', $quiz)
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage);

        $currentQuestion = new Question(); // assume null object
        if ($request->query->has('question')) {
            $currentQuestion = $this->questionRepository->find($request->query->getInt('question'));
        }

        $paginator = new Paginator($queryBuilder);

        return $this->render('quizzes/frames/_list-questions.html.twig', [
            'page' => $page,
            'currentQuiz' => $quiz,
            'currentQuestion' => $currentQuestion,
            'questions' => $paginator->getIterator()->getArrayCopy(),
            'totalPages' => round($paginator->count() / $perPage, PHP_ROUND_HALF_UP)
        ]);
    }

    // @todo move to question repository, name findOrFirst
    private function getFirstUnansweredQuizQuestion(Quiz $quiz)
    {
        $result = $this->questionRepository
            ->createQueryBuilder('q')
            ->leftJoin(Answer::class, 'a', Join::WITH, 'q.id = a.question')
            ->where('q.quiz = :quizId')
            ->andWhere('a.id IS NULL')
            ->orderBy('q.id')
            ->setParameter('quizId', $quiz->getId())
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    public function getQuizQuestionById(Quiz $quiz, ?int $questionId): ?Question
    {
        $result = $this->questionRepository
            ->createQueryBuilder('q')
            ->orderBy('q.id')
            ->where('q.quiz = :quizId')
            ->andWhere('q.id = :id')
            ->setParameter('quizId', $quiz->getId())
            ->setParameter('id', $questionId)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    public function getLastQuizQuestion(Quiz $quiz): ?Question
    {
        $result = $this->questionRepository
            ->createQueryBuilder('q')
            ->orderBy('q.id', 'DESC')
            ->where('q.quiz = :quizId')
            ->setParameter('quizId', $quiz->getId())
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();

        return $result[0] ?? null;
    }

    public function getFirstQuiz(): ?Quiz
    {
        // @todo get from user available quizzes
        return $this->quizRepository
            ->createQueryBuilder('q')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
