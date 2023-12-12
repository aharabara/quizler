<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app/quiz-time")]
class QuizTimeController extends AbstractController
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

    public function handleForm(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Quiz $quiz */
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    #[Route('/{quiz}/question/{question}', name: 'go_through_quiz', defaults: ['question' => null])]
    public function index(Request $request, Quiz $quiz, ?Question $question = null): Response
    {
        $question ??= $this->findFirstQuestionOrNull($quiz);
        $question ??= (new Question())
            ->setQuiz($quiz)
            ->setAuthor($this->getUser());

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

        return $this->render('quizzes/quiz-time.html.twig', [
            'currentQuiz' => $quiz,
            'currentQuestion' => $question,
            'currentAnswer' => new Answer(),
            'questions' => $queryBuilder
                ->getQuery()
                ->getResult(),
        ]);
    }

    public function findFirstQuestionOrNull(Quiz $quiz): ?Question
    {
        try {
            return $this->questionRepository
                ->createQueryBuilder('q')
                ->leftJoin(Answer::class, 'a', Join::WITH, 'q.id = a.question')
                ->where('q.quiz = :quiz')
                ->andWhere('a.value IS NULL')
                ->setParameter('quiz', $quiz->getId())
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            return null;
        }
    }
}
