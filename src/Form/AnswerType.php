<?php

namespace App\Form;

use App\Entity\Answer;
use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Answer $answer */
        $answer = $options['data'];
        $isEditMode = !is_null($answer->getId());
        $builder
            ->add('value', TextareaType::class, [
                'required' => true,
                'label' => false,
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                ],
                'empty_data' => '',
                'attr' => [
                    'placeholder' => 'Answer here...'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => $isEditMode ? 'Edit' : 'Create',
                'attr' => [
                    'class' => $isEditMode ? 'btn-warning' : 'btn-success',
                    'data-turbo-submits-with' => $isEditMode ? 'Updating...' : 'Saving...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
        ]);
    }
}
