<?php

namespace App\Controller\CRUD;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use App\Representation\RepresentAs;
use App\Representation\RepresentationType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    )
    {
        parent::__construct($this->entityManager);
    }

    #[Route("/create", name: "question_create", methods: ['POST', 'GET'])]
    #[Route("/{question}/edit", name: "question_edit", methods: ['POST', 'GET'])]
    #[RepresentAs(RepresentationType::FORM_SUBMITTED, redirectRoute: 'go_through_quiz', routeParams : ['quiz', 'question'])]
    #[RepresentAs(RepresentationType::TURBO, template: '/CRUD/question/frames/_form.html.twig')]
    #[RepresentAs(RepresentationType::HTML, template: '/CRUD/question/form.html.twig')]
    public function createQuiz(Request $request, Quiz $quiz, ?int $question = null): array
    {
        if (!is_null($question)){
            $question = $this->questionRepository->findOneBy(['quiz' => $quiz->getId(), 'id' => $question]);
        }

        $question ??= (new Question())
            ->setQuiz($quiz)
            ->setAuthor($this->getUser());

        $form = $this->createForm(QuestionType::class, $question, [
            'action' => $request->getRequestUri()
        ]);

        if ($this->handleForm($form, $request)) {
            $this->addFlash('success', "Question '{$quiz->getValue()}' was updated.");
        }

        return [
            'form' => $form,
            'quiz' => $quiz,
            'question' => $question
        ];
    }

    #[Route("/{question}/delete", name: "question_delete", methods: ['DELETE'])]
    #[RepresentAs(RepresentationType::REDIRECT, redirectRoute: 'go_through_quiz', routeParams: ['quiz'] )]
    public function deleteQuestion(Question $question): array
    {
        $quizId = $question->getQuiz()->getId();
        $questionId = $question->getId();

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        $this->addFlash('warning', "Question '{$question->getValue()}' with ID:{$questionId} was deleted.");

        return [
            'quiz' => $quizId
        ];
    }
}
