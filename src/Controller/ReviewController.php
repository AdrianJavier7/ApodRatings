<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\FotoAstralRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReviewController extends AbstractController
{
    #[Route('/review/{fecha}', name: 'app_review')]
    public function index(Request $request, string $fecha, FotoAstralRepository $fotoAstralRepository, EntityManagerInterface $entityManager): Response
    {
        if($request->isMethod('POST')) {
            $comentario = $request->request->get('comentario');
            $estrellas = $request->request->get('estrellas');
            $usuariologueado = $this->getUser();

            if($usuariologueado == null) {
                $this->addFlash('error', 'Debes iniciar sesión para dejar una reseña.');
                return $this->redirectToRoute('app_login');
            } else {
                $usuariologueado = $this->getUser();
            }


            $fecha2 = new \DateTime($fecha);

            // Guardamos la reseña
            $review = new Review();
            $review->setComentario($comentario);
            $review->setEstrellas($estrellas);
            $review->setUsuario($usuariologueado);
            $foto = $fotoAstralRepository->findOneBy(['date' => $fecha2]);
            $review->setFotoAstral($foto);
            $entityManager->persist($review);
            $entityManager->flush();

            // Redirigimos de vuelta a la página de la foto astral
            $this->addFlash('success', '¡Gracias por tu reseña!');
            return $this->redirectToRoute('app_foto_astral_dentro', [
                'date' => $fecha
            ]);
        }



        return $this->render('review/index.html.twig', [
            'controller_name' => 'ReviewController',
            'fecha' => $fecha,
        ]);
    }
}
