<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use App\Repository\FotoAstralRepository;
use App\Repository\RankingFotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoriaController extends AbstractController
{
    #[Route('/categoria', name: 'app_categoria')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoriaRepository $categoriaRepository,
        FotoAstralRepository $fotoAstralRepository
    ): Response {

        if ($request->isMethod('POST')) {

            // CREAR CATEGORÍA
            if ($request->request->has('nombre_categoria')) {
                $nombre = $request->request->get('nombre_categoria');
                $url = $request->request->get('foto_categoria');

                if (!$categoriaRepository->findOneBy(['nombre' => $nombre])) {
                    $nueva_categoria = new Categoria();
                    $nueva_categoria->setNombre($nombre);
                    $nueva_categoria->setImagen($url);
                    $entityManager->persist($nueva_categoria);
                    $this->addFlash('success', 'Nueva categoría registrada en el sistema.');
                } else {
                    $this->addFlash('error', 'El nombre de la categoría ya existe.');
                }
            }


            if ($request->request->has('asociar_lote')) {
                $fotosIds = $request->request->all('fotos_ids');
                $catId = $request->request->get('categoria_id');
                $categoria = $categoriaRepository->find($catId);

                if ($categoria && !empty($fotosIds)) {
                    foreach ($fotosIds as $id) {
                        $foto = $fotoAstralRepository->find($id);
                        if ($foto) {
                            $categoria->addFotoAstral($foto);
                        }
                    }
                    $this->addFlash('success', count($fotosIds) . ' fotografías vinculadas con éxito.');
                }
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_categoria');
        }

        return $this->render('categoria/categorias.html.twig', [
            'todas_fotos' => $fotoAstralRepository->findAll(),
            'categorias' => $categoriaRepository->findAll(),
        ]);


    }

    #[Route('/categorias', name: 'app_categoria_mostrar')]
    public function categorias(
        Request $request,
        CategoriaRepository $categoriaRepository
    ): Response {
        $busqueda = $request->query->get('q');

        if ($busqueda) {
            $categorias = $categoriaRepository->buscarPorNombre($busqueda);
        } else {
            $categorias = $categoriaRepository->findAll();
        }

        return $this->render('categoria/mostrar.html.twig', [
            'categorias' => $categorias,
        ]);
    }

    #[Route('/categoria/edit/{id}', name: 'app_categoria_edit', methods: ['POST'])]
    public function edit(int $id, CategoriaRepository $catRepo, Request $request, EntityManagerInterface $em): Response
    {
        $categoria = $catRepo->find($id);

        if (!$categoria) {
            $this->addFlash('error', 'La categoría no existe.');
            return $this->redirectToRoute('app_categoria');
        }

        $nuevoNombre = $request->request->get('nombre_categoria');
        $nuevaFoto = $request->request->get('foto_categoria');

        if ($nuevoNombre && $nuevaFoto) {
            $categoria->setNombre($nuevoNombre);
            $categoria->setImagen($nuevaFoto);

            $em->flush();
            $this->addFlash('success', 'Categoría actualizada correctamente.');
        }

        return $this->redirectToRoute('app_categoria');
    }

    #[Route('/categoria/quitar-foto/{categoriaId}/{fotoId}', name: 'app_categoria_quitar_foto')]
    public function quitarFoto(
        int $categoriaId,
        int $fotoId,
        CategoriaRepository $categoriaRepository,
        FotoAstralRepository $fotoRepository,
        RankingFotoRepository $rfRepo,
        EntityManagerInterface $em
    ): Response {
        $categoria = $categoriaRepository->find($categoriaId);
        $foto = $fotoRepository->find($fotoId);

        if ($categoria && $foto) {
            $rankingsFotosAEliminar = $rfRepo->createQueryBuilder('rf')
                ->join('rf.ranking', 'r')
                ->where('r.categoria = :categoria')
                ->andWhere('rf.fotoAstral = :foto')
                ->setParameter('categoria', $categoria)
                ->setParameter('foto', $foto)
                ->getQuery()
                ->getResult();

            foreach ($rankingsFotosAEliminar as $rf) {
                $em->remove($rf);
            }

            // 2. Desvinculamos la foto de la categoría (tu lógica actual)
            $categoria->removeFotoAstral($foto);

            $em->flush();
            $this->addFlash('success', 'Foto eliminada de la categoría y de los rankings de usuarios.');
        }

        return $this->redirectToRoute('app_categoria'); // O la ruta que prefieras
    }

    #[Route('/categoria/anyadir-foto', name: 'app_categoria_anyadir_foto', methods: ['POST'])]
    public function anyadirFoto(Request $request, CategoriaRepository $catRepo, FotoAstralRepository $fotoRepo, EntityManagerInterface $em): Response
    {
        $categoriaId = $request->request->get('categoria_id');
        $fotoId = $request->request->get('foto_id');

        $categoria = $catRepo->find($categoriaId);
        $foto = $fotoRepo->find($fotoId);

        if ($categoria && $foto) {
            $categoria->addFotoAstral($foto);
            $em->flush();
        }

        return $this->redirectToRoute('app_categoria');
    }

    #[Route('/categoria/delete/{id}', name: 'app_categoria_delete')]
    public function delete(Categoria $categoria, EntityManagerInterface $em): Response
    {
        $em->remove($categoria);
        $em->flush();
        $this->addFlash('success', 'Categoría eliminada correctamente.');
        return $this->redirectToRoute('app_categoria');
    }
}
