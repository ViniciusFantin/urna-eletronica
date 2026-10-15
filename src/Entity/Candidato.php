<?php

namespace App\Entity;

use App\Repository\CandidatoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatoRepository::class)]
#[ORM\UniqueConstraint(
    name: 'UNIQ_CANDIDATO_NUMERO',
    fields: ['numero']
)]
class Candidato
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $nome = null;

    #[ORM\Column]
    private ?int $numero = null;

    #[ORM\Column(length: 100)]
    private ?string $cargo = null;

    // Nullable para preservar possíveis candidatos já cadastrados.
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $partido = null;

    #[ORM\OneToOne(targetEntity: Eleitor::class)]
    #[ORM\JoinColumn(
        name: 'eleitor_id',
        referencedColumnName: 'id',
        nullable: true,
        unique: true
    )]
    private ?Eleitor $eleitor = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function getCargo(): ?string
    {
        return $this->cargo;
    }

    public function setCargo(string $cargo): static
    {
        $this->cargo = $cargo;
        return $this;
    }

    public function getPartido(): ?string
    {
        return $this->partido;
    }

    public function setPartido(string $partido): static
    {
        $this->partido = $partido;
        return $this;
    }

    public function getEleitor(): ?Eleitor
    {
        return $this->eleitor;
    }

    public function setEleitor(?Eleitor $eleitor): static
    {
        $this->eleitor = $eleitor;
        return $this;
    }
}