<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UsuarioController extends AbstractController
{
    #[Route('/registro', name: 'app_usuario_registro')]
    public function registro(Request $request,
                             UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {

        if($request->isMethod('POST')) {
            if($entityManager->getRepository(Usuario::class)->findOneBy([
                'email' => $request->request->get('email')
            ])) {
                $this->addFlash('error', 'Ya existe un usuario con ese email');
                return $this->redirectToRoute('app_usuario_registro');
            }

            $nuevo_usuario = new Usuario();
            $nuevo_usuario->setEmail($request->request->get('email'));
            $nuevo_usuario->SetRol(['ROLE_USER']);

            $password_text = $request->request->get('password');
            $password_hash = $passwordHasher->hashPassword($nuevo_usuario, $password_text);
            $nuevo_usuario->setPassword($password_hash);

            $entityManager->persist($nuevo_usuario);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');

        }



        return $this->render('usuario/registro.html.twig', [
            'controller_name' => 'UsuarioController',
        ]);
    }

}
