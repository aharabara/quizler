<?php

namespace Quiz\ORM\Traits;

trait Timestampable
{
    protected ?\DateTimeInterface $createdAt = null;
    protected ?\DateTimeInterface $updatedAt = null;

    public function getCreatedAt(): \DateTimeInterface
    {
        if (null === $this->createdAt) {
            $this->createdAt = date_create();
        }
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        if (null === $this->updatedAt) {
            $this->updatedAt = date_create();
        }
        return $this->updatedAt;
    }

    public function setCreatedAt(\DateTimeInterface|int|null $createdAt): void
    {
        if (is_int($createdAt)) {
            $createdAt = new \DateTimeImmutable(date(\DateTimeImmutable::ATOM, $createdAt));
        }
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(\DateTimeInterface|int|null $updatedAt): void
    {
        if (is_int($updatedAt)) {
            $updatedAt = new \DateTimeImmutable(date(\DateTimeImmutable::ATOM, $updatedAt));
        }
        $this->updatedAt = $updatedAt;
    }
}