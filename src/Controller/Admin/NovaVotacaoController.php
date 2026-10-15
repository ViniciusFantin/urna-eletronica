<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/eleicao')]
#[IsGranted('ROLE_ADMIN')]
class NovaVotacaoController extends AbstractController
{
    #[Route(
        '/nova',
        name: 'admin_nova_votacao',
        methods: ['POST']
    )]
    public function novaVotacao(
        Request $request,
        Connection $connection
    ): Response {
        if (!$this->isCsrfTokenValid(
            'nova_votacao',
            (string) $request->request->get('_csrf_token', '')
        )) {
            throw $this->createAccessDeniedException(
                'Token de segurança inválido.'
            );
        }

        $connection->beginTransaction();

        try {
            // Bloqueia o registro da eleição durante o reinício.
            $eleicao = $connection->fetchAssociative(
                'SELECT id, status
                 FROM eleicao
                 WHERE id = 1
                 FOR UPDATE'
            );

            if (!$eleicao) {
                throw new \RuntimeException(
                    'Eleição não encontrada.'
                );
            }

            if ($eleicao['status'] === 'ABERTA') {
                $connection->rollBack();

                $this->addFlash(
                    'error',
                    'Encerre a votação atual antes de iniciar outra.'
                );

                return $this->redirectToRoute('admin_dashboard');
            }

            // Apaga os votos da simulação anterior.
            $connection->executeStatement(
                'DELETE FROM voto'
            );

            // Permite que todos os eleitores votem novamente.
            $connection->executeStatement(
                'UPDATE eleitor SET has_voted = 0'
            );

            // Reinicia o estado da eleição.
            $connection->executeStatement(
                "UPDATE eleicao
                 SET status = 'PENDENTE',
                     iniciada_em = NULL,
                     encerrada_em = NULL
                 WHERE id = 1"
            );

            $connection->commit();

            $this->addFlash(
                'success',
                'Nova votação preparada! Todos os eleitores poderão votar novamente.'
            );

        } catch (\Throwable $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            throw $e;
        }

        return $this->redirectToRoute('admin_dashboard');
    }
}