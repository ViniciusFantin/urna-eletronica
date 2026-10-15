<?php

namespace App\Controller\Admin;

use App\Entity\Candidato;
use App\Repository\CandidatoRepository;
use App\Repository\EleitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/candidatos')]
#[IsGranted('ROLE_ADMIN')]
class CandidatoController extends AbstractController
{
    #[Route('', name: 'admin_candidatos', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EleitorRepository $eleitorRepository,
        CandidatoRepository $candidatoRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $erro = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid(
                'cadastrar_candidato',
                (string) $request->request->get('_csrf_token', '')
            )) {
                throw $this->createAccessDeniedException(
                    'Token CSRF inválido.'
                );
            }

            $eleitorId = (int) $request->request->get('eleitor_id', 0);
            $numeroTexto = trim(
                (string) $request->request->get('numero', '')
            );
            $partido = trim(
                (string) $request->request->get('partido', '')
            );
            $cargo = trim(
                (string) $request->request->get('cargo', '')
            );

            $eleitor = $eleitorRepository->find($eleitorId);

            if (!$eleitor) {
                $erro = 'Selecione um eleitor válido.';
            } elseif (in_array(
                'ROLE_ADMIN',
                $eleitor->getRoles(),
                true
            )) {
                $erro = 'Mesários não podem ser candidatos neste modelo.';
            } elseif (!preg_match('/^[0-9]{2}$/', $numeroTexto)) {
                $erro = 'O número deve conter exatamente dois dígitos.';
            } elseif ($partido === '' || mb_strlen($partido) > 100) {
                $erro = 'Informe um partido de até 100 caracteres.';
            } elseif ($cargo === '' || mb_strlen($cargo) > 100) {
                $erro = 'Informe um cargo válido.';
            } elseif ($candidatoRepository->findOneBy([
                'numero' => (int) $numeroTexto
            ])) {
                $erro = 'Este número já está sendo utilizado.';
            } elseif ($candidatoRepository->findOneBy([
                'eleitor' => $eleitor
            ])) {
                $erro = 'Este eleitor já possui uma candidatura.';
            } else {
                $candidato = new Candidato();

                $candidato->setEleitor($eleitor);
                $candidato->setNome($eleitor->getNome());
                $candidato->setNumero((int) $numeroTexto);
                $candidato->setPartido($partido);
                $candidato->setCargo($cargo);

                $entityManager->persist($candidato);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Candidato cadastrado com sucesso!'
                );

                return $this->redirectToRoute('admin_candidatos');
            }
        }

        $eleitoresDisponiveis = [];

        foreach (
            $eleitorRepository->findBy([], ['nome' => 'ASC'])
            as $eleitor
        ) {
            if (in_array('ROLE_ADMIN', $eleitor->getRoles(), true)) {
                continue;
            }

            if ($candidatoRepository->findOneBy([
                'eleitor' => $eleitor
            ])) {
                continue;
            }

            $eleitoresDisponiveis[] = $eleitor;
        }

        return $this->render('admin/candidatos.html.twig', [
            'candidatos' => $candidatoRepository->findBy(
                [],
                ['numero' => 'ASC']
            ),
            'eleitoresDisponiveis' => $eleitoresDisponiveis,
            'erro' => $erro
        ]);
    }
}