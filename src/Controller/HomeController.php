<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app")]
class HomeController extends AbstractController
{
    public function __construct(
        protected QuizRepository     $quizRepository,
        protected QuestionRepository $questionRepository
    )
    {
    }

    #[Route('/quiz/{quiz}', name: 'app_quiz', defaults: ['quiz' => null])]
    public function index(Request $request, ?int $quiz): Response
    {
        $questionId = $request->query->getInt('question');
        $quiz = $this->getQuizByIdOrFirst($quiz);
        $question = $this->getQuestion($quiz, $questionId);

        return $this->render('main.html.twig', [
            'currentQuiz' => $quiz,
            'currentQuestion' => $question
        ]);
    }


    #[Route('/quizzes/{quiz}/list', name: 'app_quiz_list')]
    public function quizzes(Request $request, ?int $quiz): Response
    {
        $currentQuiz = $this->getQuizByIdOrFirst($quiz);
        $page = max($request->query->get('quizListPage', 1), 1);
        $perPage = 10;

        $queryBuilder = $this->quizRepository
            ->createQueryBuilder('q')
            ->setMaxResults($perPage)
            ->orderBy('q.answered', 'DESC')
            ->setFirstResult(($page - 1) * $perPage);

        $paginator = new Paginator($queryBuilder);

        return $this->render('frames/_list-quizzes.html.twig', [
            'quizzes' => $paginator->getIterator()->getArrayCopy(),
            'currentQuiz' => $currentQuiz,
            'hasNextPage' => ($paginator->count() - $perPage * $page) > 0,
            'hasPreviousPage' => $page > 1,
            'page' => $page
        ]);
    }

    #[Route('/{quiz}/question', name: 'app_quiz_questions')]
    public function questions(Request $request, ?int $quiz): Response
    {
        $quiz = $this->quizRepository->find($quiz);
        if (!$quiz) {
            throw new NotFoundHttpException('Quiz not found.');
        }

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


        $paginator = new Paginator($queryBuilder);
        return $this->render('frames/_list-questions.html.twig', [
            'page' => $page,
            'currentQuiz' => $quiz,
            'questions' => $paginator->getIterator()->getArrayCopy(),
            'totalPages' => round($paginator->count() / $perPage, PHP_ROUND_HALF_UP)
        ]);
    }

    // @todo move to question repository, name findOrFirst
    public function getQuestion(Quiz $quiz, ?int $questionId): ?Question
    {
        $queryBuilder = $this->questionRepository
            ->createQueryBuilder('q')
            ->where('q.quiz = :quizId')
            ->setParameter('quizId', $quiz->getId());

        if (!empty($questionId)) {
            $queryBuilder
                ->andWhere('q.id = :id')
                ->setParameter('id', $questionId);
        }
        return $queryBuilder
            ->getQuery()
            ->setMaxResults(1)
            ->getSingleResult();
    }

    // @todo move to quiz repository
    public function getQuizByIdOrFirst(?int $quiz): mixed
    {
        if ($quiz) {
            $quiz = $this->quizRepository->find($quiz);
        } else {
            // @todo get from user available quizzes
            $quiz = $this->quizRepository->createQueryBuilder('q')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        }
        return $quiz;
    }
}
