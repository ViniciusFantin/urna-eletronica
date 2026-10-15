<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:gerar-chaves-voto', description: 'Gera chaves do sistema de votação uma única vez')]
class GerarChavesVotoCommand extends Command
{
    public function __construct(#[Autowire('%kernel.project_dir%')] private readonly string $projectDir)
    {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!extension_loaded('sodium')) {
            $output->writeln('<error>Extensão PHP sodium não está instalada.</error>');
            return Command::FAILURE;
        }
        $dir = $this->projectDir . '/var/secure';
        $nomes = ['vote_public.key', 'vote_private.key', 'hmac.key'];
        foreach ($nomes as $nome) {
            if (file_exists($dir . '/' . $nome)) {
                $output->writeln('<error>Já existem chaves. Operação cancelada para não invalidar os votos.</error>');
                return Command::FAILURE;
            }
        }
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
            throw new \RuntimeException('Não foi possível criar var/secure.');
        }
        $par = sodium_crypto_box_keypair();
        $dados = [
            'vote_public.key' => sodium_crypto_box_publickey($par),
            'vote_private.key' => sodium_crypto_box_secretkey($par),
            'hmac.key' => random_bytes(32)
        ];
        foreach ($dados as $nome => $conteudo) {
            $caminho = $dir . '/' . $nome;
            if (file_put_contents($caminho, base64_encode($conteudo) . "\n", LOCK_EX) === false) {
                throw new \RuntimeException('Erro ao gravar chave ' . $nome);
            }
            @chmod($caminho, 0600);
        }
        $output->writeln('<info>Chaves geradas em var/secure. Faça backup seguro; não envie ao Git.</info>');
        return Command::SUCCESS;
    }
}
