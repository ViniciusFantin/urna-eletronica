<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UrnaController extends AbstractController
{
    #[Route('/', name: 'app_urna', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }
}