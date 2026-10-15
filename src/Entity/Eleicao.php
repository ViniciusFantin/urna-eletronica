<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'eleicao')]
class Eleicao
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id = 1;

    #[ORM\Column(length: 20)]
    private string $status = 'PENDENTE';

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $iniciadaEm = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $encerradaEm = null;

    public function getId(): int { return $this->id; }
    public function getStatus(): string { return $this->status; }
    public function getIniciadaEm(): ?\DateTimeImmutable { return $this->iniciadaEm; }
    public function getEncerradaEm(): ?\DateTimeImmutable { return $this->encerradaEm; }
}
