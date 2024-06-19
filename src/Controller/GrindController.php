<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/new-app/quiz-time", name: 'new_')]
class GrindController extends AbstractController
{
    public function __construct(
        protected QuizRepository         $quizRepository,
        protected QuestionRepository     $questionRepository,
        protected AnswerRepository       $answerRepository,
        protected EntityManagerInterface $entityManager,
        protected Security               $security,
    ) {
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

    #[Route('/{quiz}/question/{question}', name: 'grind', defaults: ['question' => null])]
    public function index(Quiz $quiz, ?Question $question = null): Response
    {
        $question ??= $this->findFirstQuestionOrNull($quiz);
        $question ??= (new Question())
            ->setQuiz($quiz)
            ->setAuthor($this->getUser());

        return $this->render('quizzes/quiz-time.html.twig', [
            'currentQuiz' => $quiz,
            'currentQuestion' => $question,
            'currentAnswer' => new Answer(),
            'questionToEdit' => new Question()
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
