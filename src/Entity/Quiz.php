<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\QuizRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            order: ['value' => 'ASC'],
            normalizationContext: ['groups' => ['api:quiz:list']]
        ),
        new Get(normalizationContext: ['groups' => [
            'api:quiz:list',
            'api:quiz:read',
            'api:question:list',
            'api:question:read',
            'api:answer:list',
            ]
        ]),
        new Delete()
    ]
)]
class Quiz
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['export_extra', 'api:quiz:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['export', 'api:quiz:list'])]
    private ?string $value = null;

    #[ORM\Column]
    #[Groups(['export', 'api:quiz:list'])]
    private ?int $version = null;

    #[ORM\Column]
    #[Groups(['api:quiz:list'])]
    private ?int $answered = null;

    #[ORM\Column]
    #[Groups(['api:quiz:list'])]
    private ?int $total = null;

    #[ORM\OneToMany(mappedBy: 'quiz', targetEntity: Question::class, cascade: ['persist', 'merge', 'remove'])]
    #[Groups(['export', 'api:quiz:read', 'api:question:list', 'api:question:read'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $questions;

    #[ORM\Column]
    #[Groups(['export_extra', 'api:quiz:list'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['export_extra', 'api:quiz:list'])]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuiz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getQuiz() === $this) {
                $question->setQuiz(null);
            }
        }

        return $this;
    }

    public function getAnswered(): ?int
    {
        return $this->answered;
    }

    public function setAnswered(?int $answered): void
    {
        $this->answered = $answered;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    #[PrePersist]
    public function beforeSave(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->recalculateStats();
    }

    #[ORM\PreUpdate]
    public function beforeUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();

        $this->recalculateStats();
    }

    public function recalculateStats(): void
    {
        $this->total = $this->questions->count();
        $this->answered = 0;
        foreach ($this->questions as $question) {
            /* @var Question $question */
            $this->answered += $question->getAnswers()->filter(fn(Answer $answer) => $answer->isCorrect())->count();
        }
    }
}
