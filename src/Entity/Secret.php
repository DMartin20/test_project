<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'secrets')]
class Secret
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $hash;

    #[ORM\Column(type: 'text')]
    private string $secretText;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $expiresAt;

    #[ORM\Column(type: 'integer')]
    private int $remainingViews;

    // Getterek és setterek...

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = sha1($hash);

        return $this;
    }

    public function getSecretText(): ?string
    {
        return $this->secretText;
    }

    public function setSecretText(string $secretText): self
    {
        $this->secretText = $secretText;

        $this->setHash(random_bytes(5) . $this->secretText . random_bytes(5));

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(int $minutes): self
    {
        if ($minutes > 0) {
            $this->expiresAt = (new \DateTimeImmutable())->modify('+' . $minutes . 'minutes');
        } else {
            $this->expiresAt = null; // or handle the case where $minutes is 0 or negative
        }

        return $this;
    }

    public function getRemainingViews(): ?int
    {
        return $this->remainingViews;
    }

    public function setRemainingViews(int $remainingViews): self
    {
        $this->remainingViews = $remainingViews;

        return $this;
    }

    public function minusRemainingViews(): self
    {
        if ($this->remainingViews > 0) {
            $this->remainingViews--;
        }

        return $this;
    }

}