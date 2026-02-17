<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use App\Repository\FotoAstralRepository;
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
        EntityManagerInterface $entityManager,
        CategoriaRepository $categoriaRepository,
        FotoAstralRepository $fotoAstralRepository
    ): Response {
        return $this->render('categoria/mostrar.html.twig', [
            'categorias' => $categoriaRepository->findAll(),
        ]);
    }
}
