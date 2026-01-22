<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FotoAstralController extends AbstractController
{
    #[Route('/foto/astral', name: 'app_foto_astral')]
    public function index(): Response
    {
        return $this->render('foto_astral/index.html.twig', [
            'controller_name' => 'FotoAstralController',
        ]);
    }
}
