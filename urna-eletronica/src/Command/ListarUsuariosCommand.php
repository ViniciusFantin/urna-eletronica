<?php

namespace App\Command;

use App\Repository\EleitorRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:listar-usuarios',
    description: 'Lista mesários e eleitores cadastrados'
)]
class ListarUsuariosCommand extends Command
{
    public function __construct(
        private EleitorRepository $repository
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $usuarios = $this->repository->findBy(
            [],
            ['nome' => 'ASC']
        );

        $mesarios = [];
        $eleitores = [];

        foreach ($usuarios as $usuario) {
            $linha = [
                $usuario->getId(),
                $usuario->getNome(),
                $usuario->getTituloEleitor(),
                $usuario->hasVoted() ? 'Sim' : 'Não',
            ];

            if (in_array('ROLE_ADMIN', $usuario->getRoles(), true)) {
                $mesarios[] = $linha;
            } else {
                $eleitores[] = $linha;
            }
        }

        $this->mostrarTabela($output, 'MESÁRIOS', $mesarios);
        $this->mostrarTabela($output, 'ELEITORES', $eleitores);

        $output->writeln(sprintf(
            "\n<info>Total: %d usuários (%d mesários, %d eleitores).</info>",
            count($usuarios),
            count($mesarios),
            count($eleitores)
        ));

        $output->writeln(
            '<comment>Senhas não são exibidas.</comment>'
        );

        return Command::SUCCESS;
    }

    private function mostrarTabela(
        OutputInterface $output,
        string $titulo,
        array $linhas
    ): void {
        $output->writeln("\n<info>$titulo</info>");

        if ($linhas === []) {
            $output->writeln('Nenhum usuário encontrado.');
            return;
        }

        (new Table($output))
            ->setHeaders([
                'ID',
                'Nome',
                'Título de eleitor',
                'Já votou?',
            ])
            ->setRows($linhas)
            ->render();
    }
}