<?php

namespace App\Controller\Admin;

use App\Service\AuditoriaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/auditoria')]
#[IsGranted('ROLE_ADMIN')]
class AuditoriaController extends AbstractController
{
    #[Route('', name: 'admin_auditoria', methods: ['GET'])]
    public function index(AuditoriaService $auditoria): Response
    {
        $registros = $auditoria->consultar();

        $integro = !in_array(
            false,
            array_column($registros, 'integro'),
            true
        );

        $response = $this->render(
            'admin/auditoria.html.twig',
            [
                'registros' => $registros,
                'integro' => $integro,
            ]
        );

        $response->headers->set(
            'Cache-Control',
            'private, no-store'
        );

        return $response;
    }
}