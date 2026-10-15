<?php

namespace App\Controller\Admin;

use App\Repository\CandidatoRepository;
use App\Repository\EleitorRepository;
use App\Service\CriptografiaVoto;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        Connection $db,
        CandidatoRepository $repositorio,
        EleitorRepository $eleitorRepository,
        CriptografiaVoto $crypto
    ): Response {

        // Consulta o estado atual da eleição.
        $status = $db->fetchOne(
            'SELECT status FROM eleicao WHERE id = 1'
        ) ?: 'PENDENTE';

        // Todos os usuários cadastrados podem votar,
        // incluindo eleitores comuns e mesários.
        $totalEleitores = $eleitorRepository->count([]);

        // Conta os participantes sem excluir administradores.
        // Esta consulta não revela a escolha de cada eleitor.
        $participantes = (int) $db->fetchOne(
            'SELECT COUNT(*)
             FROM eleitor
             WHERE has_voted = 1'
        );

        // Busca os candidatos cadastrados.
        $candidatos = $repositorio->findBy(
            [],
            ['numero' => 'ASC']
        );

        $resultado = [];
        $brancos = 0;
        $erroApuracao = null;

        // Os resultados somente são apurados
        // depois do encerramento da eleição.
        if ($status === 'ENCERRADA') {

            // Inicializa a contagem dos candidatos.
            foreach ($candidatos as $candidato) {
                $numero = sprintf(
                    '%02d',
                    $candidato->getNumero()
                );

                $resultado[$numero] = 0;
            }

            try {

                // Recupera apenas as cédulas,
                // sem consultar dados pessoais dos eleitores.
                $cedulas = $db->fetchAllAssociative(
                    'SELECT
                        token,
                        conteudo_cifrado,
                        assinatura
                     FROM voto'
                );

                foreach ($cedulas as $cedula) {

                    // Verifica a integridade e decifra a escolha.
                    $escolha = $crypto->decifrar(
                        $cedula['token'],
                        $cedula['conteudo_cifrado'],
                        $cedula['assinatura']
                    );

                    if ($escolha === 'BRANCO') {

                        $brancos++;

                    } elseif (array_key_exists(
                        $escolha,
                        $resultado
                    )) {

                        $resultado[$escolha]++;

                    } else {

                        throw new \RuntimeException(
                            'Cédula contém escolha desconhecida.'
                        );
                    }
                }

                // Verifica se cada participação corresponde
                // a uma cédula armazenada.
                if (count($cedulas) !== $participantes) {
                    throw new \RuntimeException(
                        'Divergência entre participação e quantidade de cédulas.'
                    );
                }

            } catch (\Throwable $e) {

                // Não apresenta resultados caso a apuração
                // encontre uma falha de integridade.
                $resultado = [];
                $brancos = 0;

                $erroApuracao =
                    'Falha de integridade/contagem. ' .
                    'Não divulgue resultados antes de auditar ' .
                    'os dados e as chaves.';
            }
        }

        $response = $this->render(
            'admin/dashboard.html.twig',
            [
                'statusEleicao' => $status,
                'totalEleitores' => $totalEleitores,
                'participantes' => $participantes,
                'candidatos' => $candidatos,
                'resultado' => $resultado,
                'brancos' => $brancos,
                'erroApuracao' => $erroApuracao,
            ]
        );

        // Impede que informações administrativas
        // sejam armazenadas em cache.
        $response->headers->set(
            'Cache-Control',
            'private, no-store, max-age=0'
        );

        return $response;
    }
}