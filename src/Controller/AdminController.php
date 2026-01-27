<?php

namespace App\Controller;

use App\Entity\FotoAstral;
use App\Repository\FotoAstralRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
        # Respuesta de la api

        $fecha_hoy = new \DateTime();
        $fecha_solo = $fecha_hoy->format('Y-m-d');

        # Fecha de hace 30 dias
        $fecha_hoy->modify('-30 days');
        $fecha_hace_30_dias_solo = $fecha_hoy->format('Y-m-d');


        $respuesta = $httpClient->request(
            'GET',
            'https://api.nasa.gov/planetary/apod?api_key=JUmT9kLz0PPTahUwzhRTGweJesmzj4fUN8P9gFdb&start_date=' . $fecha_hace_30_dias_solo . '&end_date=' . $fecha_solo
        );

        # Metemos la respuesta en la base de datos
        $contenido = $respuesta->getContent();
        $datos = json_decode($contenido, true);

        foreach ($datos as $item) {

            $fecha = new \DateTime($item['date']);
            $fotoExistente = $fotoAstralRepository->findOneBy(['date' => $fecha]);

            if (!$fotoExistente) {
                $fotoAstral = new FotoAstral();
                $fotoAstral->setDate($fecha);

                $fotoAstral->setCopyright($item['copyright'] ?? 'Public Domain');
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
}
