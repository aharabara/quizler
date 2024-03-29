<?php

namespace App\Controller\CRUD;

use App\Entity\Answer;
use App\Entity\Question;
use App\Form\AnswerType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app/question/{question}/answer")]
class AnswerCRUDController extends CRUDController
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

    #[Route(path: '/', name: 'answer_create', methods: ['POST', 'GET'])]
    #[Route(path: '/{answer}', name: 'answer_edit', methods: ['POST', 'GET'])]
    #[RepresentAs(RepresentationType::FORM_SUBMITTED, redirectRoute: 'answer_create', routeParams: ['quiz', 'question'])]
    #[RepresentAs(RepresentationType::TURBO, template: '/CRUD/answer/frames/_form.html.twig', turboFrame: 'form-answer')]
    #[RepresentAs(RepresentationType::HTML, template: '/CRUD/answer/form.html.twig')]
    public function answer(Request $request, Question $question, ?int $answer = null): array
    {
        if (!is_null($answer)) {
            $answer = $this->answerRepository->findOneBy([
                'id' => $answer, 'question' => $question->getId()
            ]);

            if (!$answer) {
                throw new NotFoundHttpException();
            }
        } else {
            $answer = (new Answer())
                ->setAuthor($this->security->getUser())
                ->setQuestion($question)
                ->setCorrect(true)
                ->setCreatedAt(new \DateTimeImmutable());
        }


        $form = $this->createForm(AnswerType::class, $answer, [
            'action' => $request->getRequestUri()
        ]);

        if ($this->handleForm($form, $request)) {
            if (!$this->currentRouteIs($request, 'answer_create')) {
                $this->addFlash('success', "Answer was created.");
            } else {
                $this->addFlash('success', "Answer was updated.");
            }
        }

        return [
            'form' => $form,
            'quiz' => $question->getQuiz(),
            'question' => $question,
            'answer' => $answer,
        ];
    }

    #[Route('/{answer}', name: 'answer_delete', methods: ['DELETE'])]
    #[RepresentAs(RepresentationType::REDIRECT, redirectRoute: 'answer_create', routeParams: ['quiz', 'question'])]
    public function deleteAnswer(Request $request, ?Answer $answer): array
    {
        $question = $answer->getQuestion();
        $quiz = $question->getQuiz();

        $this->entityManager->remove($answer);
        $this->entityManager->flush($answer);

        $this->addFlash('warning', "Answer was deleted.");

        return [
            'quiz' => $quiz,
            'question' => $question,
        ];
    }

    /*@fixme replace with vote up/down for an answer. */
//    #[Route('/quizzler/answers/{answer}/', name: 'app_quiz_answer_toggle', methods: ['POST'])]
//    public function toggleAnswerValidity(Request $request, ?Answer $answer): Response
//    {
//        $answer->setCorrect(!$answer->isCorrect());
//
//        $this->entityManager->persist($answer);
//        $this->entityManager->flush($answer);
//
//        return new RedirectResponse(
//            $this->generateUrl('app_quiz_questions', [
//                'quiz' => $request->request->get('quiz'),
//                'question' => $request->request->get('question'),
//                'questionsPage' => $request->request->get('questionsPage'),
//            ]),
//            Response::HTTP_SEE_OTHER
//        );
//    }
}
