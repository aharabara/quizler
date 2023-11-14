<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\EntityListener\AnswerListener;
use App\Repository\AnswerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\EntityListeners([AnswerListener::class])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Post(denormalizationContext: ['groups' => [self::GROUP_CREATE]]),
        new Get(normalizationContext: ['groups' => [self::GROUP_LIST, self::GROUP_READ]]),
        new GetCollection(
            paginationItemsPerPage: 200,
            order: ['id' => 'DESC'],
            normalizationContext: ['groups' => [self::GROUP_LIST]]
        ),
    ],
    order: ['id' => 'ASC']
)]
#[ApiFilter(SearchFilter::class, properties: ['question.quiz' => 'exact',])]
class Answer
{
    const GROUP_READ = 'answer:read';
    const GROUP_LIST = 'answer:list';
    const GROUP_CREATE = 'answer:create';
    const GROUP_EXPORT_EXTRA = 'export_extra';
    const EXPORT = 'export';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::GROUP_EXPORT_EXTRA, self::GROUP_LIST, self::GROUP_READ])]
    private ?int $id = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Groups([self::EXPORT, self::GROUP_LIST, self::GROUP_CREATE, self::GROUP_READ])]
    #[Assert\NotNull()]
    #[Assert\NotBlank()]
    private ?string $value = null;

    #[ORM\Column]
    #[Groups([self::EXPORT, self::GROUP_LIST, self::GROUP_CREATE, self::GROUP_READ])]
    private bool $correct = true;

    #[ORM\Column]
    #[Groups([self::GROUP_EXPORT_EXTRA, self::GROUP_LIST, self::GROUP_READ])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups([self::GROUP_EXPORT_EXTRA, self::GROUP_LIST, self::GROUP_READ])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_CREATE, self::GROUP_READ])]
    #[Assert\NotNull()]
    private ?Question $question = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::GROUP_EXPORT_EXTRA, self::GROUP_LIST, self::GROUP_READ, self::GROUP_CREATE])]
    #[Assert\NotNull()]
    private ?User $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function isCorrect(): ?bool
    {
        return $this->correct;
    }

    public function setCorrect(bool $correct): static
    {
        $this->correct = $correct;

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

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    #[SerializedName('questionText')]
    #[Groups([self::GROUP_LIST])]
    public function getQuestionText(): string
    {
        return $this->question->getValue();
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }


    #[PrePersist]
    public function beforeSave(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        /* @fixme trigger question recalculation when a new answer is created. */
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
        return "{$this->value}";
    }
}
