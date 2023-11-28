<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/app/question/{question}/answer")]
class AnswerCRUDController extends AbstractController
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

    #[Route(
        path: '/{answer}',
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
                $this->generateUrl('go_through_quiz', [
                    'quiz' => $question->getQuiz()->getId(),
                    'question' => $question->getId(),
                    'answer' => $answer->getId()
                ]),
                Response::HTTP_SEE_OTHER
            );

        }
        $this->addFlash('success', 'Answer added.');
        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        return $this->redirectToRoute('go_through_quiz', $request->query->all() + [
                'quiz' => $question->getQuiz()->getId(),
                'question' => $question->getId(),
            ],
            Response::HTTP_SEE_OTHER
        );
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
