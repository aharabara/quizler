<?php

namespace Quiz\ORM\Traits;

use DateTimeInterface;
use Exception;

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

    /**
     * @throws Exception
     */
    public function setCreatedAt(\DateTimeInterface|int|null $createdAt): void
    {
        if (is_int($createdAt)) {
            $createdAt = new \DateTimeImmutable(date(DateTimeInterface::ATOM, $createdAt));
        }

        $this->createdAt = $createdAt;
    }

    /**
     * @throws Exception
     */
    public function setUpdatedAt(\DateTimeInterface|int|null $updatedAt): void
    {
        if (is_int($updatedAt)) {
            $updatedAt = new \DateTimeImmutable(date(DateTimeInterface::ATOM, $updatedAt));
        }

        $this->updatedAt = $updatedAt;
    }
}
