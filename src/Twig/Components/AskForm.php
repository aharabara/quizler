<?php

namespace App\Twig\Components;

use App\Entity\Question;
use App\Form\QuestionType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class AskForm
{
    public ?Question $question = null;

    public function __construct(
        protected FormFactoryInterface $formBuilder
    ) {

    }

    public function mount(bool $isSuccess = true)
    {
        $this->type = $isSuccess ? 'success' : 'danger';
    }


    public function getForm(Request $request): FormView
    {
        return $this->buildForm($request)->createView();
    }

    protected function buildForm(Request $request): FormInterface
    {
        return $this
            ->formBuilder
            ->create(QuestionType::class, $this->question, [
                'action' => $request->getRequestUri()
            ]);
    }


    public function handle(Request $request): bool
    {
        $form = $this->buildForm($request);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($form->getData());
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

}
