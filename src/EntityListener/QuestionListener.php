<?php

namespace App\EntityListener;

use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\PostPersist;

/* fixme AsEntityListener + AutoconfigureTag? */
class QuestionListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[PostPersist]
    public function onPostPersist(Question $question): void
    {
        $quiz = $question->getQuiz();
        $quiz->recalculateStats();
        $this->entityManager->flush();
    }

}