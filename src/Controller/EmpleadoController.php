<?php

namespace App\Controller;

use App\Entity\Empleado;
use App\Entity\Seccion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;


class EmpleadoController extends AbstractController
{
    #[Route('/', name: 'app_menu')]
    public function menu(ManagerRegistry $doctrine): Response
    {
        $repositorio = $doctrine->getRepository(Empleado::class);
        $empleados = $repositorio->findAll();

        return $this->render('menu.html.twig', [
            'empleados' => $empleados,
        ]);
    }

    #[Route('/empleado/search', name: 'search_empleado')]
    public function search(ManagerRegistry $doctrine, Request $request): Response
    {
        $repositorio = $doctrine->getRepository(Empleado::class);

        $search = $request->query->get('searchTerm') ?? '';

        $empleado = $repositorio->findByApellidos($search);

        if ($empleado) {
            return $this->render('ficha_empleado.html.twig', array(
                'empleado' => $empleado[0],
            ));
        } else {
            return $this->render('ficha_empleado.html.twig', array(
                'empleado' => null,
            ));
        }
        
        
    }

    #[Route('/empleado/{id}', name: 'app_empleado')]
    public function index(ManagerRegistry $doctrine, $id): Response
    {
        $repositorio = $doctrine->getRepository(Empleado::class);
        $empleado = $repositorio->find($id);
        
        return $this->render('ficha_empleado.html.twig', array(
            'empleado' => $empleado,
        ));
    }
}
