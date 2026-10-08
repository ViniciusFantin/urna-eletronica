<?php

namespace App\Entity;

use App\Repository\EleitorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EleitorRepository::class)]
class Eleitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $tituloEleitor = null;

    #[ORM\Column(length: 150)]
    private ?string $nome = null;

    #[ORM\Column(length: 255)]
    private ?string $senha = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $otp = null;

    #[ORM\Column]
    private ?bool $hasVoted = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTituloEleitor(): ?string
    {
        return $this->tituloEleitor;
    }

    public function setTituloEleitor(string $tituloEleitor): static
    {
        $this->tituloEleitor = $tituloEleitor;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getSenha(): ?string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): static
    {
        $this->senha = $senha;

        return $this;
    }

    public function getOtp(): ?string
    {
        return $this->otp;
    }

    public function setOtp(?string $otp): static
    {
        $this->otp = $otp;

        return $this;
    }

    public function hasVoted(): ?bool
    {
        return $this->hasVoted;
    }

    public function setHasVoted(bool $hasVoted): static
    {
        $this->hasVoted = $hasVoted;

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
}
