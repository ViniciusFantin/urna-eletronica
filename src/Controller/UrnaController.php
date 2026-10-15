<?php
namespace App\Controller;

use App\Repository\CandidatoRepository;
use App\Repository\EleitorRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UrnaController extends AbstractController
{
    #[Route('/', name: 'app_urna', methods: ['GET'])]
    #[IsGranted('ROLE_ELEITOR')]
    public function index(
        EleitorRepository $eleitorRepository,
        CandidatoRepository $candidatoRepository,
        Connection $db
    ): Response {
        $mesarios = [];
        $eleitores = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            foreach ($eleitorRepository->findBy([], ['nome' => 'ASC']) as $usuario) {
                $dados = [
                    'id' => $usuario->getId(),
                    'nome' => $usuario->getNome(),
                    'titulo' => $usuario->getTituloEleitor(),
                    'votou' => $usuario->hasVoted(),
                ];
                if (in_array('ROLE_ADMIN', $usuario->getRoles(), true)) {
                    $mesarios[] = $dados;
                } else {
                    $eleitores[] = $dados;
                }
            }
        }
        $candidatos = [];
        foreach ($candidatoRepository->findBy([], ['numero' => 'ASC']) as $candidato) {
            $numero = sprintf('%02d', $candidato->getNumero());
            $candidatos[$numero] = [
                'name' => $candidato->getNome(),
                'party' => $candidato->getPartido() . ' · ' . $numero,
            ];
        }
        $status = $db->fetchOne('SELECT status FROM eleicao WHERE id = 1');
        $response = $this->render('index.html.twig', [
            'mesarios' => $mesarios,
            'eleitores' => $eleitores,
            'candidatosUrna' => $candidatos,
            'statusEleicao' => $status ?: 'PENDENTE',
        ]);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        return $response;
    }
}
