<?php

namespace App\Controller;

use App\Entity\Apuesta;
use App\Entity\User;
use App\Entity\NumerosLoteria;
use App\Entity\Sorteo;
use App\Form\NumerosLoteriaType;
use App\Repository\NumerosLoteriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/numeros/loteria')]
class NumerosLoteriaController extends AbstractController
{
    #[Route('/', name: 'app_numeros_loteria_index', methods: ['GET'])]
    public function index(NumerosLoteriaRepository $numerosLoteriaRepository): Response
    {
        return $this->render('numeros_loteria/index.html.twig', [
            'numeros_loterias' => $numerosLoteriaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_numeros_loteria_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $numerosLoterium = new NumerosLoteria();
        $form = $this->createForm(NumerosLoteriaType::class, $numerosLoterium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($numerosLoterium);
            $entityManager->flush();

            return $this->redirectToRoute('app_numeros_loteria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('numeros_loteria/new.html.twig', [
            'numeros_loterium' => $numerosLoterium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_numeros_loteria_show', methods: ['GET'])]
    public function show(NumerosLoteria $numerosLoterium): Response
    {
        return $this->render('numeros_loteria/show.html.twig', [
            'numeros_loterium' => $numerosLoterium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_numeros_loteria_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NumerosLoteria $numerosLoterium, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NumerosLoteriaType::class, $numerosLoterium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_numeros_loteria_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('numeros_loteria/edit.html.twig', [
            'numeros_loterium' => $numerosLoterium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_numeros_loteria_delete', methods: ['POST'])]
    public function delete(Request $request, NumerosLoteria $numerosLoterium, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$numerosLoterium->getId(), $request->request->get('_token'))) {
            $entityManager->remove($numerosLoterium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_numeros_loteria_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/comprar/{id}/{sorteoId}', name: 'comprar_numero_loteria', methods: ['GET'])]
    public function comprarNumeroLoteria($id, $sorteoId, EntityManagerInterface $entityManager): Response
    {
        // Obtén el número de lotería por su ID
        $numeroLoteria = $entityManager->getRepository(NumerosLoteria::class)->find($id);
    
        if (!$numeroLoteria) {
            throw $this->createNotFoundException('No se encontró el número de lotería con el ID: ' . $id);
        }
    
        // Obtén el sorteo por su ID
        $sorteoActual = $entityManager->getRepository(Sorteo::class)->find($sorteoId);
    
        if (!$sorteoActual) {
            throw $this->createNotFoundException('No se encontró el sorteo con el ID: ' . $sorteoId);
        }
    
        // Verifica si el número de lotería ya está comprado en alguna apuesta para el mismo sorteo
        $isComprado = $entityManager->getRepository(Apuesta::class)->existsApuestaForNumeroLoteriaAndSorteo($numeroLoteria, $sorteoActual);
    
        if ($isComprado) {
            // Puedes redirigir o mostrar un mensaje indicando que el número ya ha sido comprado en ese sorteo
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteoId]);
        }
    
        // Verifica si el usuario tiene suficientes fondos para comprar el número de lotería
        $user = $this->getUser();
        $costoNumero = $sorteoActual->getCost();
    
        if ($user->getFondos() < $costoNumero) {
            // Puedes redirigir o mostrar un mensaje indicando que el usuario no tiene suficientes fondos
            $this->addFlash('error', 'No tienes suficientes fondos para comprar este número de lotería.');
            return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteoId]);
        }
    
        // Descuenta el costo del número de lotería de los fondos del usuario
        $user->setFondos($user->getFondos() - $costoNumero);
    
        // Crea una nueva apuesta para el usuario actual, el número de lotería y el sorteo actual
        $apuesta = new Apuesta();
        $apuesta->setUser($user);
        $apuesta->setNumeroLoteria($numeroLoteria);
        $apuesta->setSorteo($sorteoActual);
        //dd($user);
        $entityManager->persist($apuesta);
        $entityManager->flush();
    
        // Redirige a la página del sorteo actual
        $this->addFlash('success', 'Has comprado el número de lotería con éxito.');
        return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteoActual->getId()]);
    }
}
