<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/app/quiz/{quiz}/question")]
class QuestionCRUDController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $entityManager, protected QuestionRepository $questionRepository)
    {
    }

    #[Route("/create", name: "question_create", methods: ['POST', 'GET'])]
    #[Route("/{answer}/edit", name: "question_edit", methods: ['POST', 'GET'])]
    public function createQuiz(Request $request, Quiz $quiz, Question $question = null): Response
    {

        $question ??= (new Question())
            ->setQuiz($quiz)
            ->setAuthor($this->getUser());

        $form = $this->createForm(QuestionType::class, $question, [
            'attr' => [
                'data-turbo-action' => 'advance'
            ]
        ]);

        if ($this->handleForm($form, $request)) {
            return $this->redirectToRoute('question_list', ['search' => $question->getValue()]);
        }

        return $this->render(
            'CRUD/quiz/form.html.twig',
            [
                'form' => $form,
                'quiz' => $quiz,
                'question' => $question
            ]);
    }

    #[Route("/list", name: "question_list", methods: ['GET'])]
    public function listQuestions(Request $request): Response
    {
        $perPage = max($request->query->getInt('perPage', 1), 10);
        $page = max($request->query->getInt('page', 1), 1);
        $search = $request->query->get('search');

        $queryBuilder = $this
            ->questionRepository
            ->createQueryBuilder('question')
            ->orderBy('question.id', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage);

        if(!empty($search)) {
            $queryBuilder
                ->where('question.value LIKE :search')
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

    #[Route("/{question}/delete", name: "question_delete", methods: ['DELETE'])]
    public function deleteQuestion(Question $question): Response
    {
        $id = $question->getId();

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        $this->addFlash('success', "Question '{$question->getValue()}' with ID:{$id} was deleted.");

        return $this->redirectToRoute('question_list', status: Response::HTTP_SEE_OTHER);
    }

    public function handleForm(FormInterface $form, Request $request): bool
    {

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Quiz $quiz */
            $this->entityManager->persist($quiz = $form->getData());
            $this->entityManager->flush();
            $this->addFlash('success', "Quiz '{$quiz->getValue()}' was created.");

            return true;
        }

        return false;
    }

}
