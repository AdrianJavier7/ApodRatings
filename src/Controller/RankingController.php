<?php

namespace App\Controller;

use App\Entity\Ranking;
use App\Entity\RankingFoto;
use App\Entity\Review;
use App\Repository\CategoriaRepository;
use App\Repository\FotoAstralRepository;
use App\Repository\RankingFotoRepository;
use App\Repository\RankingRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RankingController extends AbstractController
{
    #[Route('/categoria/{id}/ranking', name: 'app_categoria_ranking')]
    public function index(
        int $id,
        CategoriaRepository $categoriaRepository,
        RankingRepository $rankingRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        FotoAstralRepository $fotoAstralRepository,
        RankingFotoRepository $rankingFotoRepository
    ): Response {

        $categoria = $categoriaRepository->find($id);
        $usuario = $this->getUser();

        if (!$usuario) {
            $this->addFlash('error', 'Debes iniciar sesión para acceder a esta sección.');
            return $this->redirectToRoute('app_login');
        }

        $ranking = $rankingRepository->findOneBy(['categoria' => $categoria, 'usuario' => $usuario]);
        if (!$ranking) {
            $ranking = new Ranking();
            $ranking->setUsuario($usuario);
            $ranking->setCategoria($categoria);
            $entityManager->persist($ranking);
            $entityManager->flush();
        }

        $totalFotos = count($categoria->getFotoAstrals());
        $fotosRankeadas = count($ranking->getRankingFotos());

        // Ahora 'yaRankeado' solo lo usaremos para cambiar textos en la vista, no para bloquear
        $yaRankeado = ($fotosRankeadas > 0 && $fotosRankeadas >= $totalFotos);

        if ($request->isMethod('POST')) {
            $posiciones = $request->request->all('posiciones');

            // Validar que no haya duplicados en las posiciones enviadas
            $valoresFiltrados = array_filter($posiciones, fn($v) => $v !== null && $v !== '');
            if (count($valoresFiltrados) !== count(array_unique($valoresFiltrados))) {
                $this->addFlash('error', 'Error: No puedes repetir la misma posición.');
                return $this->redirectToRoute('app_categoria_ranking', ['id' => $id]);
            }

            foreach ($posiciones as $fotoId => $pos) {
                if ($pos === null || $pos === '') continue;

                $foto = $fotoAstralRepository->find($fotoId);
                if ($foto) {
                    // Buscamos si ya existe esta foto en el ranking del usuario
                    $rankingFoto = $rankingFotoRepository->findOneBy([
                        'ranking' => $ranking,
                        'fotoAstral' => $foto
                    ]);

                    // Si no existe, la creamos (Nuevo ranking)
                    if (!$rankingFoto) {
                        $rankingFoto = new RankingFoto();
                        $rankingFoto->setRanking($ranking);
                        $rankingFoto->setFotoAstral($foto);
                    }

                    // Actualizamos la posición (Tanto si es nueva como si es edición)
                    $rankingFoto->setPosicion((int)$pos);
                    $entityManager->persist($rankingFoto);
                }
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', '¡Ranking guardado con éxito!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al procesar los datos del ranking.');
            }

            // Redirigimos a la lista de rankings para que vea el resultado
            return $this->redirectToRoute('app_mis_rankings');
        }

        // Preparamos las posiciones actuales para mandarlas a la vista
        $posicionesGuardadas = [];
        foreach ($ranking->getRankingFotos() as $rf) {
            $posicionesGuardadas[$rf->getFotoAstral()->getId()] = $rf->getPosicion();
        }

        return $this->render('ranking/ranking.html.twig', [
            'categoria' => $categoria,
            'ya_rankeado' => $yaRankeado,
            'posiciones_guardadas' => $posicionesGuardadas,
            'ranking_actual' => $ranking
        ]);
    }

    #[Route('/mis-rankings', name: 'app_mis_rankings')]
    public function misRankings(RankingRepository $rankingRepository): Response
    {
        $usuario = $this->getUser();
        if (!$usuario) {
            return $this->redirectToRoute('app_login');
        }

        $rankings = $rankingRepository->findBy(['usuario' => $usuario]);

        return $this->render('ranking/mis_ranking.html.twig', [
            'rankings' => $rankings
        ]);
    }

    #[Route('/estadisticas', name: 'app_estadisticas')]
    public function estadisticas(RankingFotoRepository $rfRepo, ReviewRepository $resenaRepo): Response
    {
        $statsFotos = $rfRepo->createQueryBuilder('rf')
            ->select('f.title as titulo', 'f.url as url', 'AVG(rf.posicion) as mediaPosicion', 'COUNT(rf.id) as vecesRankeada')
            ->join('rf.fotoAstral', 'f')
            ->groupBy('f.id')
            ->orderBy('mediaPosicion', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $statsEstrellas = $resenaRepo->createQueryBuilder('r')
            ->select('f.title as titulo', 'f.url as url', 'AVG(r.estrellas) as mediaEstrellas', 'COUNT(r.id) as totalVotos')
            ->join('r.fotoAstral', 'f') // Asumiendo que en Resena la relación es fotoAstral
            ->groupBy('f.id')
            ->orderBy('mediaEstrellas', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('ranking/estadisticas.html.twig', [
            'stats' => $statsFotos,
            'statsEstrellas' => $statsEstrellas
        ]);
    }

    #[Route('/review/edit/{id}', name: 'app_review_editar', methods: ['POST'])]
    public function edit(int $id, ReviewRepository $resenaRepo, Request $request, EntityManagerInterface $entityManager): Response
    {
        $resena = $resenaRepo->find($id);

        if (!$resena || $resena->getUsuario() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $resena->setEstrellas($request->request->get('estrellas'));
        $resena->setComentario($request->request->get('comentario'));

        $entityManager->flush();


        return $this->redirectToRoute('app_foto_astral_dentro', [
            'date' => $resena->getFotoAstral()->getDate()->format('Y-m-d')
        ]);
    }
}
