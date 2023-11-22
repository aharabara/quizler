<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Quiz $quiz */
        $quiz = $options['data'];
        $isEditMode = !is_null($quiz->getId());
        $builder
            ->add('value', TextType::class, [
                'required' => true,
                'label' => 'Title'
            ])
            ->add('source', UrlType::class, [
                'required' => false,
                'disabled' => $isEditMode,
                'label' => 'Source',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $isEditMode ? 'Edit' : 'Create',
                'attr' => [
                    'data-turbo-submits-with' => $isEditMode ? 'Updating...' : 'Saving...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
