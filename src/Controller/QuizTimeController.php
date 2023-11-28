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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        if ($question === null) {
            if ($question = $this->findFirstQuestionOrNull($quiz)) {
                return $this->redirectToRoute('go_through_quiz', $request->query->all() + ['quiz' => $quiz->getId(), 'question' => $question->getId()]);
            } else {
                $question = (new Question())
                    ->setQuiz($quiz);
            }
        }

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

        $newQuestion = (new Question())
            ->setAuthor($this->getUser())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setQuiz($quiz);

        $form = $this->createForm(QuestionType::class, $newQuestion);

        /**@todo
         * if form failed - > throw an event with validation errors that are then catched by an event listener that can instantly response or halt message?
         *
         * 1. rewrite render() and redirect methods
         * 2. fetch target selector from the headers if possible and use it with turbo streams
         * 3. IF params contain a form AND it was submitted AND contains constraint violations AND turbo streams are expected
         *      THEN render a stream of errors to display in UI AND render turbo stream changes that will display flash messages (or toasts)
         *      ELSE go old render flow
         * 4. IF params contain a form AND it was submitted AND submission is successful AND turbo stream is expected
         *      THEN render turbo stream that will display flash messages AND render new form AND render an additional element that will be prepended(POST)/appended(POST)/replaces (PUT/PATCH) in the target element
         *
         *
         * */
        if ($this->handleForm($form, $request)) {
            // todo think how to handle redirect/turbo/simple response
//            return $this->turbo('quizzes/frames/_list-item.html.twig', [
//                'item' => $newQuestion,
//                'currentQuestion' => $question
//            ]);
            return $this->redirectToRoute('go_through_quiz', ['quiz' => $quiz->getId(), 'question' => $newQuestion->getId()]);
        }

        return $this->render('quizzes/quiz-time.html.twig', [
            'form' => $form,
            'currentQuiz' => $quiz,
            'currentQuestion' => $question,
            'currentAnswer' => new Answer(),
            'questions' => $queryBuilder
                ->getQuery()
                ->getResult(),

        ]);
    }

    /**
     * @param array $parameters
     * @param $template
     * @return Response
     */
    public function turbo(string $template, array $parameters): Response
    {
        /*@todo WIP for turbo responses. */
        $response = new Response();
        $response->headers->add([
            'Content-Type' => 'text/vnd.turbo-stream.html; charset=utf-8'
        ]);

        return $this->render('turbo-stream.html.twig', [
            'template' => $template,
            ...$parameters
        ], response: $response);
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
