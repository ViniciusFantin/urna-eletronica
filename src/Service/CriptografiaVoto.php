<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CriptografiaVoto
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {}

    private function chave(string $arquivo): string
    {
        $variaveis = [
            'vote_public.key' => 'VOTO_PUBLIC_KEY',
            'vote_private.key' => 'VOTO_PRIVATE_KEY',
            'hmac.key' => 'VOTO_HMAC_KEY',
        ];

        if (!isset($variaveis[$arquivo])) {
            throw new \RuntimeException(
                'Tipo de chave não reconhecido.'
            );
        }

        $nomeVariavel = $variaveis[$arquivo];

        // Primeiro tenta as variáveis de ambiente do Railway.
        $valor = $_SERVER[$nomeVariavel]
            ?? $_ENV[$nomeVariavel]
            ?? getenv($nomeVariavel);

        // Se não estiver configurada, utiliza o arquivo local.
        if (!is_string($valor) || trim($valor) === '') {
            $caminho = $this->projectDir
                . '/var/secure/'
                . $arquivo;

            $valor = @file_get_contents($caminho);

            if ($valor === false) {
                throw new \RuntimeException(
                    'Chave de votação não configurada: '
                    . $nomeVariavel
                );
            }
        }

        // As chaves são armazenadas em Base64.
        $bin = base64_decode(trim($valor), true);

        if ($bin === false) {
            throw new \RuntimeException(
                'Formato de chave inválido: '
                . $nomeVariavel
            );
        }

        return $bin;
    }

    public function cifrar(string $escolha): array
    {
        if (!extension_loaded('sodium')) {
            throw new \RuntimeException(
                'A extensão Sodium não está disponível.'
            );
        }

        $publica = $this->chave('vote_public.key');
        $hmac = $this->chave('hmac.key');

        if (
            strlen($publica) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES ||
            strlen($hmac) !== 32
        ) {
            throw new \RuntimeException(
                'Chaves de votação com tamanho inválido.'
            );
        }

        // Identificador aleatório sem relação com o eleitor.
        $token = bin2hex(random_bytes(32));

        // Criptografia da escolha utilizando Sodium.
        $cifrado = base64_encode(
            sodium_crypto_box_seal($escolha, $publica)
        );

        // HMAC-SHA256 para integridade da cédula.
        $assinatura = hash_hmac(
            'sha256',
            $token . '.' . $cifrado,
            $hmac
        );

        return [
            'token' => $token,
            'cifrado' => $cifrado,
            'assinatura' => $assinatura,
        ];
    }

    public function decifrar(
        string $token,
        string $cifrado,
        string $assinatura
    ): string {
        if (!extension_loaded('sodium')) {
            throw new \RuntimeException(
                'A extensão Sodium não está disponível.'
            );
        }

        $hmac = $this->chave('hmac.key');

        if (strlen($hmac) !== 32) {
            throw new \RuntimeException(
                'Chave HMAC inválida.'
            );
        }

        $calculada = hash_hmac(
            'sha256',
            $token . '.' . $cifrado,
            $hmac
        );

        if (!hash_equals($calculada, $assinatura)) {
            throw new \RuntimeException(
                'Integridade de uma cédula comprometida.'
            );
        }

        $secreta = $this->chave('vote_private.key');
        $publica = $this->chave('vote_public.key');

        if (
            strlen($secreta) !== SODIUM_CRYPTO_BOX_SECRETKEYBYTES ||
            strlen($publica) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES
        ) {
            throw new \RuntimeException(
                'Par de chaves inválido.'
            );
        }

        $bin = base64_decode($cifrado, true);

        if ($bin === false) {
            throw new \RuntimeException(
                'Cédula cifrada inválida.'
            );
        }

        $chaves = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            $secreta,
            $publica
        );

        try {
            $texto = sodium_crypto_box_seal_open(
                $bin,
                $chaves
            );
        } finally {
            sodium_memzero($secreta);
        }

        if ($texto === false) {
            throw new \RuntimeException(
                'Não foi possível decifrar uma cédula.'
            );
        }

        return $texto;
    }
}