<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\AnswerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Post(denormalizationContext: ['groups' => ['api:answer:create']]),
        new Get(normalizationContext: ['groups' => ['api:answer:list']]),
        new GetCollection(normalizationContext: ['groups' => ['api:answer:list']]),
    ]
)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['export_extra', 'api:answer:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 2048, nullable: true)]
    #[Groups(['export', 'api:answer:list', 'api:answer:create'])]
    private ?string $value = null;

    #[ORM\Column]
    #[Groups(['export', 'api:answer:list', 'api:answer:create'])]
    private ?bool $correct = null;

    #[ORM\Column]
    #[Groups(['export_extra', 'api:answer:list'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['export_extra', 'api:answer:list'])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['api:answer:create'])]
    private ?Question $question = null;

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
    }

    #[ORM\PreUpdate]
    public function beforeUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
