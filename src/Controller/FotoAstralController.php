<?php

namespace App\Controller;

use App\Repository\FotoAstralRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(FotoAstralRepository $fotoRepo, Request $request): Response
    {
        $orden = $request->query->get('orden', 'fecha_desc');
        $fecha_limite = (new \DateTime())->modify('-30 days');

        $query = $fotoRepo->createQueryBuilder('f')
            ->leftJoin('f.reviews', 'r')
            ->select('f', 'AVG(r.estrellas) as HIDDEN media')
            ->where('f.date >= :limite')
            ->setParameter('limite', $fecha_limite)
            ->groupBy('f.id');

        if ($orden === 'fecha_asc') {
            $query->orderBy('f.date', 'ASC');
        } elseif ($orden === 'valoracion_desc') {
            $query->orderBy('media', 'DESC');
        } else {
            $query->orderBy('f.date', 'DESC');
        }

        $lista_elementos = $query->getQuery()->getResult();

        return $this->render('foto_astral/todos.html.twig', [
            'lista_foto_astral' => $lista_elementos,
            'orden_actual' => $orden
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

    #[Route('/foto-astral/{date}', name: 'app_foto_astral_dentro')]
    public function dentro(string $date, FotoAstralRepository $fotoAstralRepository): Response
    {
        $usuario = $this->getUser();
        $yaValorado = false;
        $foto = null;
        $resenas = [];

        try {
            $fecha = new \DateTime($date);
            $foto = $fotoAstralRepository->findOneBy(['date' => $fecha]);

            if ($foto) {
                $resenas = $foto->getReviews();

                if ($usuario) {
                    foreach ($resenas as $resena) {
                        if ($resena->getUsuario() === $usuario) {
                            $yaValorado = true;
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Fecha no válida o error al cargar la foto astral.');
             return $this->redirectToRoute('app_foto_astral');
        }

        return $this->render('foto_astral/dentro.html.twig', [
            'foto_astral' => $foto,
            'resenas' => $resenas,
            'ya_valorado' => $yaValorado
        ]);
    }


}
