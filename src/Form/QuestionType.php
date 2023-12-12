<?php

namespace App\Form;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContext;

class QuestionType extends AbstractType
{
    public function __construct(protected QuestionRepository $repository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Question $question */
        $question = $options['data'];
        $isEditMode = !is_null($question->getId());
        $builder
            ->add('value', TextType::class, [
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new Callback(function (?string $value, ExecutionContext $context) use ($question) {
                        if ($question->getId() !== null) {
                            return;
                        }
                        $existingQuestion = $this->repository->findOneBy([
                            'quiz' => $question->getQuiz()->getId(),
                            'value' => $value,
                        ]);
                        if ($existingQuestion !== null) {
                            $context->addViolation('This question already exists.');
                        }
                    })
                ],
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Ask here...'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $isEditMode ? 'Edit' : 'Ask',
                'attr' => [
                    'class' => $isEditMode ? 'btn btn-warning' : 'btn btn-primary',
                    'data-turbo-submits-with' => $isEditMode ? 'Updating...' : 'Saving...'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
