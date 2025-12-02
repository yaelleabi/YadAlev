<?php

namespace App\Controller;

use App\Entity\FamilyEvent;
use App\Form\FamilyEventType;
use App\Repository\FamilyEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/family/event')]
final class FamilyEventController extends AbstractController
{
    #[Route(name: 'app_family_event_index', methods: ['GET'])]
    public function index(FamilyEventRepository $familyEventRepository): Response
    {
        return $this->render('family_event/index.html.twig', [
            'family_events' => $familyEventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_family_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $familyEvent = new FamilyEvent();
        $form = $this->createForm(FamilyEventType::class, $familyEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($familyEvent);
            $entityManager->flush();

            return $this->redirectToRoute('app_family_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('family_event/new.html.twig', [
            'family_event' => $familyEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_family_event_show', methods: ['GET'])]
    public function show(FamilyEvent $familyEvent): Response
    {
        return $this->render('family_event/show.html.twig', [
            'family_event' => $familyEvent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_family_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FamilyEvent $familyEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FamilyEventType::class, $familyEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_family_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('family_event/edit.html.twig', [
            'family_event' => $familyEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_family_event_delete', methods: ['POST'])]
    public function delete(Request $request, FamilyEvent $familyEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$familyEvent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($familyEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_family_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
