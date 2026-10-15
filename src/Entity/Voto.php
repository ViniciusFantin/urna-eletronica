<?php
namespace App\Entity;

use App\Repository\VotoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: VotoRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_VOTO_TOKEN', fields: ['token'])]
class Voto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $token = null;

    // Campos legados: não são preenchidos na votação real.
    #[ORM\Column(nullable: true)]
    private ?int $candidatoId = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $conteudoCifrado = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $assinatura = null;

    public function getId(): ?int { return $this->id; }
    public function getToken(): ?string { return $this->token; }
    public function setToken(string $value): static { $this->token = $value; return $this; }
    public function getCandidatoId(): ?int { return $this->candidatoId; }
    public function setCandidatoId(?int $value): static { $this->candidatoId = $value; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $value): static { $this->createdAt = $value; return $this; }
    public function getConteudoCifrado(): ?string { return $this->conteudoCifrado; }
    public function setConteudoCifrado(string $value): static { $this->conteudoCifrado = $value; return $this; }
    public function getAssinatura(): ?string { return $this->assinatura; }
    public function setAssinatura(?string $value): static { $this->assinatura = $value; return $this; }
}
