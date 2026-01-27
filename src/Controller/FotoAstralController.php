<?php

namespace App\Controller;

use App\Repository\FotoAstralRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FotoAstralController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/foto/astral', name: 'app_foto_astral')]
    public function index(HttpClientInterface $httpClient): Response
    {
        $fecha_hoy = new \DateTime();
        $fecha_hace_30_dias = (clone $fecha_hoy)->modify('-30 days');

        $respuesta = $httpClient->request(
            'GET',
            'https://api.nasa.gov/planetary/apod?api_key=JUmT9kLz0PPTahUwzhRTGweJesmzj4fUN8P9gFdb&start_date=' . $fecha_hace_30_dias->format('Y-m-d') . '&end_date=' . $fecha_hoy->format('Y-m-d')
        );

        $lista_elementos = $respuesta->toArray();

        return $this->render('foto_astral/todos.html.twig', [
            'lista_foto_astral' => $lista_elementos,
        ]);
    }

    #[Route('/foto/diaria', name: 'app_foto_astral_diaria')]
    public function una_foto(HttpClientInterface $httpClient): Response
    {

        $respuesta = $httpClient->request(
            'GET',
            'https://api.nasa.gov/planetary/apod?api_key=JUmT9kLz0PPTahUwzhRTGweJesmzj4fUN8P9gFdb'
        );

        $elemento = $respuesta->toArray();



        return $this->render('foto_astral/solo.html.twig', [
            'foto_astral' => $elemento,
        ]);
    }

}
