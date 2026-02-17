<?php

namespace App\Controller;

use App\Entity\Ranking;
use App\Entity\RankingFoto;
use App\Repository\CategoriaRepository;
use App\Repository\FotoAstralRepository;
use App\Repository\RankingFotoRepository;
use App\Repository\RankingRepository;
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
        $yaRankeado = ($fotosRankeadas > 0 && $fotosRankeadas >= $totalFotos);

        if ($request->isMethod('POST')) {
            if ($yaRankeado) {
                $this->addFlash('error', 'Este sector ya está clasificado.');
                return $this->redirectToRoute('app_categoria_ranking', ['id' => $id]);
            }

            $posiciones = $request->request->all('posiciones');

            $valoresFiltrados = array_filter($posiciones, fn($v) => $v !== null && $v !== '');
            if (count($valoresFiltrados) !== count(array_unique($valoresFiltrados))) {
                $this->addFlash('error', 'Error: No puedes repetir la misma posición.');
                return $this->redirectToRoute('app_categoria_ranking', ['id' => $id]);
            }

            foreach ($posiciones as $fotoId => $pos) {
                if ($pos === null || $pos === '') continue;

                $foto = $fotoAstralRepository->find($fotoId);
                if ($foto) {
                    $rankingFoto = $rankingFotoRepository->findOneBy([
                        'ranking' => $ranking,
                        'fotoAstral' => $foto
                    ]);

                    if (!$rankingFoto) {
                        $rankingFoto = new RankingFoto();
                        $rankingFoto->setRanking($ranking);
                        $rankingFoto->setFotoAstral($foto);
                    }

                    $rankingFoto->setPosicion((int)$pos);
                    $entityManager->persist($rankingFoto);
                }
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', '¡Ranking actualizado!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al guardar el ranking.');
            }

            return $this->redirectToRoute('app_categoria_ranking', ['id' => $id]);
        }

        $posicionesGuardadas = [];
        foreach ($ranking->getRankingFotos() as $rf) {
            $posicionesGuardadas[$rf->getFotoAstral()->getId()] = $rf->getPosicion();
        }

        return $this->render('ranking/ranking.html.twig', [
            'categoria' => $categoria,
            'ya_rankeado' => $yaRankeado,
            'posiciones_guardadas' => $posicionesGuardadas
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
    public function estadisticas(RankingFotoRepository $rfRepo): Response
    {
        $statsFotos = $rfRepo->createQueryBuilder('rf')
            ->select('f.title as titulo', 'f.url as url', 'AVG(rf.posicion) as mediaPosicion', 'COUNT(rf.id) as vecesRankeada')
            ->join('rf.fotoAstral', 'f')
            ->groupBy('f.id')
            ->orderBy('mediaPosicion', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('ranking/estadisticas.html.twig', [
            'stats' => $statsFotos
        ]);
    }
}
