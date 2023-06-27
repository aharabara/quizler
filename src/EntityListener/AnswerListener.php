<?php

namespace App\EntityListener;

use App\Entity\Answer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\PostPersist;

/* fixme AsEntityListener + AutoconfigureTag? */
class AnswerListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[PostPersist]
    public function onPostPersist(Answer $answer): void
    {
        $quiz = $answer->getQuestion()->getQuiz();
        $quiz->recalculateStats();
        $this->entityManager->flush();
    }

}