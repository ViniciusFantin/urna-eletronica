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
        $caminho = $this->projectDir . '/var/secure/' . $arquivo;
        $valor = @file_get_contents($caminho);
        if ($valor === false) {
            throw new \RuntimeException('Chaves de votação não configuradas em var/secure.');
        }
        $bin = base64_decode(trim($valor), true);
        if ($bin === false) {
            throw new \RuntimeException('Formato de chave de votação inválido.');
        }
        return $bin;
    }

    public function cifrar(string $escolha): array
    {
        $publica = $this->chave('vote_public.key');
        $hmac = $this->chave('hmac.key');
        if (strlen($publica) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES || strlen($hmac) !== 32) {
            throw new \RuntimeException('Chave de votação com tamanho inválido.');
        }
        $token = bin2hex(random_bytes(32));
        $cifrado = base64_encode(sodium_crypto_box_seal($escolha, $publica));
        $assinatura = hash_hmac('sha256', $token . '.' . $cifrado, $hmac);
        return ['token' => $token, 'cifrado' => $cifrado, 'assinatura' => $assinatura];
    }

    public function decifrar(string $token, string $cifrado, string $assinatura): string
    {
        $hmac = $this->chave('hmac.key');
        $calculada = hash_hmac('sha256', $token . '.' . $cifrado, $hmac);
        if (!hash_equals($calculada, $assinatura)) {
            throw new \RuntimeException('Integridade de uma cédula comprometida.');
        }
        $secreta = $this->chave('vote_private.key');
        $publica = $this->chave('vote_public.key');
        if (strlen($secreta) !== SODIUM_CRYPTO_BOX_SECRETKEYBYTES || strlen($publica) !== SODIUM_CRYPTO_BOX_PUBLICKEYBYTES) {
            throw new \RuntimeException('Par de chaves inválido.');
        }
        $bin = base64_decode($cifrado, true);
        if ($bin === false) {
            throw new \RuntimeException('Cédula cifrada inválida.');
        }
        $chaves = sodium_crypto_box_keypair_from_secretkey_and_publickey($secreta, $publica);
        $texto = sodium_crypto_box_seal_open($bin, $chaves);
        sodium_memzero($secreta);
        if ($texto === false) {
            throw new \RuntimeException('Não foi possível decifrar uma cédula.');
        }
        return $texto;
    }
}
