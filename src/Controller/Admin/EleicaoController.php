<?php
namespace App\Controller\Admin;

use App\Service\CriptografiaVoto;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/eleicao')]
#[IsGranted('ROLE_ADMIN')]
class EleicaoController extends AbstractController
{
    #[Route('/abrir', name: 'admin_eleicao_abrir', methods: ['POST'])]
    public function abrir(Request $request, Connection $db): Response
    {
        if (!$this->isCsrfTokenValid('eleicao_abrir', (string)$request->request->get('_csrf_token', ''))) {
            throw $this->createAccessDeniedException();
        }
        $db->beginTransaction();
        try {
            $db->executeStatement("INSERT INTO eleicao (id, status) VALUES (1, 'PENDENTE') ON DUPLICATE KEY UPDATE id = id");
            $linha = $db->fetchAssociative('SELECT status FROM eleicao WHERE id = 1 FOR UPDATE');
            if ($linha['status'] !== 'PENDENTE') {
                $this->addFlash('error', 'Esta eleição já foi iniciada ou encerrada.');
            } elseif ((int)$db->fetchOne('SELECT COUNT(*) FROM candidato') < 1) {
                $this->addFlash('error', 'Cadastre ao menos um candidato antes de abrir a eleição.');
            } else {
                $db->executeStatement("UPDATE eleicao SET status = 'ABERTA', iniciada_em = NOW() WHERE id = 1");
                $this->addFlash('success', 'Votação aberta com sucesso.');
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->isTransactionActive()) { $db->rollBack(); }
            $this->addFlash('error', 'Não foi possível abrir a votação.');
        }
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/encerrar', name: 'admin_eleicao_encerrar', methods: ['POST'])]
    public function encerrar(Request $request, Connection $db): Response
    {
        if (!$this->isCsrfTokenValid('eleicao_encerrar', (string)$request->request->get('_csrf_token', ''))) {
            throw $this->createAccessDeniedException();
        }
        $db->beginTransaction();
        try {
            $linha = $db->fetchAssociative('SELECT status FROM eleicao WHERE id = 1 FOR UPDATE');
            if (!$linha || $linha['status'] !== 'ABERTA') {
                $this->addFlash('error', 'A eleição não está aberta.');
            } else {
                $db->executeStatement("UPDATE eleicao SET status = 'ENCERRADA', encerrada_em = NOW() WHERE id = 1");
                $this->addFlash('success', 'Eleição encerrada. A apuração está disponível.');
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->isTransactionActive()) { $db->rollBack(); }
            $this->addFlash('error', 'Erro ao encerrar votação.');
        }
        return $this->redirectToRoute('admin_dashboard');
    }
}
