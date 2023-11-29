<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\EntityListener\QuestionListener;
use App\Repository\QuestionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\EntityListeners([QuestionListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(fields: ['quiz', 'value'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => [self::GROUP_LIST]]),
        new Get(normalizationContext: ['groups' => [self::GROUP_LIST, self::GROUP_READ]]),
        new Post(
            normalizationContext: ['groups' => [self::GROUP_LIST, self::GROUP_READ]],
            denormalizationContext: ['groups' => [self::QUESTION_CREATE]]
        ),
    ],
    order: ['id' => 'DESC'],
)]
class Question
{
    const GROUP_LIST = 'question:list';
    const GROUP_READ = 'question:read';
    const QUESTION_CREATE = 'question:create';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['export_extra', self::GROUP_LIST])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['export', self::GROUP_LIST, self::QUESTION_CREATE])]
    #[Assert\NotBlank(), Assert\NotNull()]
    private ?string $value = null;

    #[ORM\Column]
    #[Groups(['export_extra', self::GROUP_LIST])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['export_extra', self::GROUP_LIST])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Answer::class, cascade: ['persist', 'merge', 'remove'], fetch: 'LAZY')]
    #[Groups(['export', self::GROUP_READ])]
    private Collection $answers;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::QUESTION_CREATE])]
    #[Assert\NotNull()]
    private ?Quiz $quiz = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Question author should be specified.")]
    #[Groups([self::QUESTION_CREATE])]
    private ?User $author = null;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): string
    {
        return "$this->value";
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

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
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): static
    {
        $this->quiz = $quiz;

        return $this;
    }


    #[PrePersist]
    public function beforeSave(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function beforeUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function __toString(): string
    {
        return "$this->id";
    }
}
