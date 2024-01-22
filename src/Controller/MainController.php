<?php

namespace App\Controller;

use App\Repository\SorteoRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(SorteoRepository $sorteoRepository, EntityManagerInterface $entityManager): Response
    {
         $this->realizarSorteo($sorteoRepository,$entityManager);
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }

    public function realizarSorteo( $sorteoRepository, $entityManager){
        $sorteos = $sorteoRepository->findSorteosPasados();
        // dd($sorteos);
        // die;
        foreach($sorteos as $sorteo){
            $numerosLoteria = $sorteo->getNumerosLoteria()->toArray();
            $sorteo->setWinner($numerosLoteria[array_rand($numerosLoteria)]->getNumero());
            $sorteo->setState(1);
            $entityManager->persist($sorteo);
        }
        
        $entityManager->flush();
    }
}
