<?php

namespace App\Controller;

use App\Entity\FotoAstral;
use App\Entity\Ranking;
use App\Entity\Review;
use App\Repository\CategoriaRepository;
use App\Repository\FotoAstralRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
final class AdminController extends AbstractController
{
    /**
     * @throws \DateMalformedStringException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ORMException
     */
    #[Route('/admin', name: 'app_admin')]
    public function index(HttpClientInterface $httpClient,
                          FotoAstralRepository $fotoAstralRepository,
                          EntityManagerInterface $entityManager): Response
    {

        $fecha_hoy = new \DateTime();
        $fecha_solo = $fecha_hoy->format('Y-m-d');

        $fecha_hoy->modify('-30 days');
        $fecha_hace_30_dias_solo = $fecha_hoy->format('Y-m-d');


        $respuesta = $httpClient->request(
            'GET',
            'https://api.nasa.gov/planetary/apod?api_key=JUmT9kLz0PPTahUwzhRTGweJesmzj4fUN8P9gFdb&start_date=' . $fecha_hace_30_dias_solo . '&end_date=' . $fecha_solo
        );

        $contenido = $respuesta->getContent();
        $datos = json_decode($contenido, true);

        foreach ($datos as $item) {

            $fecha = new \DateTime($item['date']);
            $fotoExistente = $fotoAstralRepository->findOneBy(['date' => $fecha]);

            if (!$fotoExistente) {
                $fotoAstral = new FotoAstral();
                $fotoAstral->setDate($fecha);

                $fotoAstral->setCopyright($item['copyright'] ?? 'Dominio Público');
                $fotoAstral->setExplanation($item['explanation'] ?? 'Sin descripción');
                $fotoAstral->setHdurl($item['hdurl'] ?? '');
                $fotoAstral->setMediaType($item['media_type'] ?? 'image');
                $fotoAstral->setTitle($item['title'] ?? 'Sin título');
                $fotoAstral->setUrl($item['url'] ?? '');

                $entityManager->persist($fotoAstral);
            }
        }
        $entityManager->flush();



        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/gestion', name: 'app_admin_gestion')]
    #[IsGranted('ROLE_ADMIN')]
    public function gestion(
        EntityManagerInterface $entityManager,
        FotoAstralRepository $fotoRepo,
        CategoriaRepository $catRepo,
        UsuarioRepository $userRepo
    ): Response {
        $stats = [
            'total_fotos'      => $fotoRepo->count([]),
            'total_categorias' => $catRepo->count([]),
            'total_usuarios'   => $userRepo->count([]),
            'total_rankings'   => $entityManager->getRepository(Ranking::class)->count([]),
            'total_resenas'    => $entityManager->getRepository(Review::class)->count([]),
        ];

        $ultimosRankings = $entityManager->getRepository(Ranking::class)
            ->findBy([], ['id' => 'DESC'], 5);

        $ultimosUsuarios = $userRepo->findBy([], ['id' => 'DESC'], 5);

        return $this->render('admin/gestion.html.twig', [
            'stats'           => $stats,
            'ultimosRankings' => $ultimosRankings,
            'ultimosUsuarios' => $ultimosUsuarios,
        ]);
    }
}
