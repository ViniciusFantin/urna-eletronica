<?php

namespace App\Entity;

use App\Repository\VotoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VotoRepository::class)]
class Voto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $token = null;

    #[ORM\Column]
    private ?int $candidatoId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $assinatura = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getCandidatoId(): ?int
    {
        return $this->candidatoId;
    }

    public function setCandidatoId(int $candidatoId): static
    {
        $this->candidatoId = $candidatoId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAssinatura(): ?string
    {
        return $this->assinatura;
    }

    public function setAssinatura(?string $assinatura): static
    {
        $this->assinatura = $assinatura;

        return $this;
    }
}
