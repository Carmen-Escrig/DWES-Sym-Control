<?php

namespace App\Controller;

use App\Entity\Empleado;
use App\Entity\Seccion;

use App\Form\SeccionFormType;
use App\Form\EmpleadoFormType;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;



class AdminController extends AbstractController
{
    #[Route('/admin/seccion_add', name: 'add_seccion')]
    public function addSection(ManagerRegistry $doctrine, Request $request): Response
    {
        $seccion = new Seccion();
        $formulario = $this->createForm(SeccionFormType::class, $seccion);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $seccion = $formulario->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($seccion);
            try {
                $entityManager->flush();
                return $this->redirectToRoute('app_menu');
            } catch (\Exception $e) {
                return new Response("Error" . $e->getMessage());
            }
        }
        return $this->render('nueva_seccion.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }

    #[Route('/admin/seccion_delete/{id}', name: 'delete_seccion')]
    public function deleteSection(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Seccion::class);
        $seccion = $repositorio->find($id);

        if($seccion) {
            try {
                $entityManager->remove($seccion);
                $entityManager->flush();
                return $this->redirectToRoute('app_menu');
            } catch (\Exception $e) {
                return new Response("Error" . $e->getMessage());
            }
        } else {
            return new Response("Seccion no encontrada");
        }
    }

    #[Route('/admin/empleado_add', name: 'add_empleado')]
    public function addEmpleado(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {
        $empleado = new Empleado();
        $formulario = $this->createForm(EmpleadoFormType::class, $empleado);

        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $foto = $formulario->get('foto')->getData();
            if ($foto) {
                $originalFilename = pathinfo($foto->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$foto->guessExtension();

                try {

                    $foto->move(
                        $this->getParameter('images_directory'), $newFilename
                    );
                    $filesystem = new Filesystem();
        
                } catch (FileException $e) {
                    return new Response("Error" . $e->getMessage());
                }
                $empleado->setFoto($newFilename);
            }
            $empleado = $formulario->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($empleado);
            try {
                $entityManager->flush();
                return $this->redirectToRoute('app_empleado', [
                    "id" => $empleado->getId()
                ]);
            } catch (\Exception $e) {
                return new Response("Error" . $e->getMessage());
            }
        }
        return $this->render('nuevo_empleado.html.twig', array(
            'formulario' => $formulario->createView()
        ));
    }

    #[Route('/admin/empleado_delete/{id}', name: 'delete_empleado')]
    public function deleteEmpleado(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Empleado::class);
        $empleado = $repositorio->find($id);

        if($empleado) {
            try {
                $entityManager->remove($empleado);
                $entityManager->flush();
                return $this->redirectToRoute('app_menu');
            } catch (\Exception $e) {
                return new Response("Error" . $e->getMessage());
            }
        } else {
            return new Response("Empleado no encontrado");
        }
    }
}
