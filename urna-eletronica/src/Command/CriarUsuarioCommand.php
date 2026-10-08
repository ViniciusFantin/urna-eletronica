<?php

namespace App\Command;

use App\Entity\Eleitor;
use App\Repository\EleitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:criar-usuario',
    description: 'Cadastra eleitor ou mesário para testes'
)]
class CriarUsuarioCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EleitorRepository $repository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $helper = $this->getHelper('question');

        $nome = trim((string) $helper->ask(
            $input,
            $output,
            new Question('Nome: ')
        ));

        $titulo = trim((string) $helper->ask(
            $input,
            $output,
            new Question('Título de eleitor fictício (12 dígitos): ')
        ));

        if (
            $nome === '' ||
            mb_strlen($nome) > 150 ||
            !preg_match('/^[0-9]{12}$/', $titulo)
        ) {
            $output->writeln(
                '<error>Nome ou título inválido.</error>'
            );

            return Command::FAILURE;
        }

        if ($this->repository->findOneBy([
            'tituloEleitor' => $titulo
        ])) {
            $output->writeln(
                '<error>Título já cadastrado.</error>'
            );

            return Command::FAILURE;
        }

        $perfil = $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                'Perfil:',
                ['Eleitor', 'Mesário'],
                0
            )
        );

        $question = new Question('Senha: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $senha = $helper->ask(
            $input,
            $output,
            $question
        );

        if (!is_string($senha) || strlen($senha) < 12) {
            $output->writeln(
                '<error>A senha deve conter pelo menos 12 caracteres.</error>'
            );

            return Command::FAILURE;
        }

        $eleitor = new Eleitor();

        $eleitor->setNome($nome);
        $eleitor->setTituloEleitor($titulo);
        $eleitor->setHasVoted(false);
        $eleitor->setCreatedAt(new \DateTimeImmutable());

        $eleitor->setRoles(
            $perfil === 'Mesário'
                ? ['ROLE_ADMIN']
                : ['ROLE_ELEITOR']
        );

        $hash = $this->passwordHasher->hashPassword(
            $eleitor,
            $senha
        );

        $eleitor->setSenha($hash);

        $this->entityManager->persist($eleitor);
        $this->entityManager->flush();

        $output->writeln(
            '<info>Usuário cadastrado com sucesso!</info>'
        );

        return Command::SUCCESS;
    }
}