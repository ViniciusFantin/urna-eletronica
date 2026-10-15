<?php

namespace App\Controller\Api;

use App\Entity\Eleitor;
use App\Service\CriptografiaVoto;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/votos')]
#[IsGranted('ROLE_ELEITOR')]
class VotoController extends AbstractController
{
    #[Route(
        '/confirmar',
        name: 'api_voto_confirmar',
        methods: ['POST']
    )]
    public function confirmar(
        Request $request,
        Connection $db,
        CriptografiaVoto $crypto
    ): JsonResponse {
        // Mesários também são eleitores e podem votar.
        // A permissão ROLE_ELEITOR já é exigida no controller.

        if (!$this->isCsrfTokenValid(
            'confirmar_voto',
            (string) $request->headers->get('X-CSRF-TOKEN', '')
        )) {
            return $this->json([
                'erro' => 'Sessão inválida. Recarregue a página.'
            ], 403);
        }

        $usuario = $this->getUser();

        if (!$usuario instanceof Eleitor || $usuario->getId() === null) {
            return $this->json([
                'erro' => 'Identificação inválida.'
            ], 401);
        }

        try {
            $dados = json_decode(
                $request->getContent(),
                true,
                8,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return $this->json([
                'erro' => 'Dados inválidos.'
            ], 400);
        }

        $numero = is_array($dados)
            ? ($dados['numero'] ?? null)
            : null;

        if (
            $numero !== 'BRANCO' &&
            (
                !is_string($numero) ||
                !preg_match('/^[0-9]{2}$/', $numero)
            )
        ) {
            return $this->json([
                'erro' => 'Escolha inválida.'
            ], 422);
        }

        try {
            // Criptografa somente a escolha.
            // Não anexa ID, nome, título ou perfil do eleitor.
            $cedula = $crypto->cifrar($numero);
        } catch (\Throwable) {
            return $this->json([
                'erro' => 'Serviço de votação indisponível.'
            ], 503);
        }

        $db->beginTransaction();

        try {
            // Bloqueia a eleição para serializar os registros
            // e as alterações de estado.
            $eleicao = $db->fetchAssociative(
                'SELECT status
                 FROM eleicao
                 WHERE id = 1
                 FOR UPDATE'
            );

            if (
                !$eleicao ||
                $eleicao['status'] !== 'ABERTA'
            ) {
                $db->rollBack();

                return $this->json([
                    'erro' => 'A votação não está aberta.'
                ], 409);
            }

            // Valida o candidato no banco.
            if ($numero !== 'BRANCO') {
                $existe = $db->fetchOne(
                    'SELECT id FROM candidato WHERE numero = ?',
                    [(int) $numero]
                );

                if ($existe === false) {
                    $db->rollBack();

                    return $this->json([
                        'erro' => 'Candidato não encontrado.'
                    ], 422);
                }
            }

            // Impede voto duplicado por meio de atualização atômica.
            // Funciona igualmente para eleitores e mesários.
            $alterados = $db->executeStatement(
                'UPDATE eleitor
                 SET has_voted = 1
                 WHERE id = ?
                 AND has_voted = 0',
                [$usuario->getId()]
            );

            if ($alterados !== 1) {
                $db->rollBack();

                return $this->json([
                    'erro' => 'Voto já registrado para esta conta.'
                ], 409);
            }

            // A cédula não possui vínculo com a conta.
            $db->insert('voto', [
                'token' => $cedula['token'],
                'candidato_id' => null,
                'created_at' => null,
                'conteudo_cifrado' => $cedula['cifrado'],
                'assinatura' => $cedula['assinatura']
            ]);

            $db->commit();

            return $this->json([
                'ok' => true,
                'mensagem' => 'Voto registrado com sucesso.'
            ]);

        } catch (\Throwable $e) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }

            return $this->json([
                'erro' => 'Não foi possível registrar o voto. Tente novamente.'
            ], 500);
        }
    }
}