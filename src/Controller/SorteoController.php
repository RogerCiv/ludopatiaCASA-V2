<?php

namespace App\Controller;

use App\Entity\Sorteo;
use App\Form\NumerosLoteria;
use App\Form\SorteoType;
use App\Repository\ApuestaRepository;
use App\Repository\NumerosLoteriaRepository;
use App\Repository\SorteoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sorteo')]
class SorteoController extends AbstractController
{
  #[Route('/', name: 'app_sorteo_index', methods: ['GET'])]
  public function index(SorteoRepository $sorteoRepository): Response
  {
    return $this->render('sorteo/index.html.twig', [
      'sorteos' => $sorteoRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_sorteo_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $entityManager, NumerosLoteriaRepository $numerosLoteriaRepository): Response
  {
    $sorteo = new Sorteo();
    $form = $this->createForm(SorteoType::class, $sorteo);
    $form->handleRequest($request);
    $numLoterias = $numerosLoteriaRepository->findAll();

    if ($form->isSubmitted() && $form->isValid()) {
      $sorteo->setFechaInicio(new \DateTime());
      $sorteo->setState(0);
      $sorteo->setCobrado(0);
      $costoNumeroLoteria = $sorteo->getCost();

      // Calcula el 50% del precio de cada número de lotería para este sorteo
      $prize = count($numLoterias) * $costoNumeroLoteria * 0.5;

      // Establece el Prize en el 50% del precio
      $sorteo->setPrize($prize);

      foreach ($numLoterias as $numeroLoteria) {
        $sorteo->addNumerosLoterium($numeroLoteria);
      }
      $entityManager->persist($sorteo);
      $entityManager->flush();

      return $this->redirectToRoute('app_sorteo_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('sorteo/new.html.twig', [
      'sorteo' => $sorteo,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_sorteo_show', methods: ['GET'])]
  public function show(Sorteo $sorteo, ApuestaRepository $apuestaRepository): Response
  {

    $numerosLoteria = $sorteo->getNumerosLoteria();
    $comprasUsuario = [];

    foreach ($numerosLoteria as $numeroLoteria) {
      $comprasUsuario[$numeroLoteria->getId()] = $apuestaRepository
        ->existsApuestaForNumeroLoteriaAndSorteo($numeroLoteria, $sorteo);
    }
    return $this->render('sorteo/show.html.twig', [
      'sorteo' => $sorteo,
      'comprasUsuario' => $comprasUsuario,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_sorteo_edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, Sorteo $sorteo, EntityManagerInterface $entityManager): Response
  {
    $form = $this->createForm(SorteoType::class, $sorteo);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager->flush();

      return $this->redirectToRoute('app_sorteo_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('sorteo/edit.html.twig', [
      'sorteo' => $sorteo,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_sorteo_delete', methods: ['POST'])]
  public function delete(Request $request, Sorteo $sorteo, EntityManagerInterface $entityManager): Response
  {
    if ($this->isCsrfTokenValid('delete' . $sorteo->getId(), $request->request->get('_token'))) {
      $entityManager->remove($sorteo);
      $entityManager->flush();
    }

    return $this->redirectToRoute('app_sorteo_index', [], Response::HTTP_SEE_OTHER);
  }

  #[Route('/realizar_sorteo_manual/{id}', name: 'realizar_sorteo_manual', methods: ['GET'])]
  public function realizarSorteoManual(Sorteo $sorteo, EntityManagerInterface $entityManager): Response
  {
    // Realiza el sorteo manualmente

    $numerosLoteria = $sorteo->getNumerosLoteria();


    $numeroGanador = $numerosLoteria[array_rand($numerosLoteria->toArray())]->getNumero();
    $sorteo->setWinner($numeroGanador);

    $sorteo->setState(1);
    $entityManager->flush();

    return $this->redirectToRoute('app_sorteo_show', ['id' => $sorteo->getId()]);
  }
}
