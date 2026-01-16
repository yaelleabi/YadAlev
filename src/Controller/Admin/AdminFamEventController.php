<?php

namespace App\Controller\Admin;

use App\Entity\FamilyEvent;
use App\Form\FamilyEventType;
use App\Repository\FamilyEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(attribute: 'ROLE_ADMIN')]
#[Route('/admin/fam/event')]
final class AdminFamEventController extends AbstractController
{
    #[Route('/', name: 'admin_family_event_index', methods: ['GET'])]
    public function index(FamilyEventRepository $familyEventRepository): Response
    {
        return $this->render('admin/family_event/index.html.twig', [
            'family_events' => $familyEventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_family_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $familyEvent = new FamilyEvent();
        $form = $this->createForm(FamilyEventType::class, $familyEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($familyEvent);
            $entityManager->flush();

            return $this->redirectToRoute('admin_family_event_index');
        }

        return $this->render('admin/family_event/new.html.twig', [
            'family_event' => $familyEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/requests', name: 'admin_family_event_requests', methods: ['GET'])]
    public function requests(FamilyEvent $familyEvent): Response
    {
        return $this->render('admin/family_event/requests.html.twig', [
            'family_event' => $familyEvent,
        ]);
    }

    #[Route('/{id}', name: 'admin_family_event_show', methods: ['GET'])]
    public function show(FamilyEvent $familyEvent): Response
    {
        return $this->render('admin/family_event/show.html.twig', [
            'family_event' => $familyEvent,
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_family_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FamilyEvent $familyEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FamilyEventType::class, $familyEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_family_event_index');
        }

        return $this->render('admin/family_event/edit.html.twig', [
            'family_event' => $familyEvent,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_family_event_delete', methods: ['POST'])]
    public function delete(Request $request, FamilyEvent $familyEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$familyEvent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($familyEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_family_event_index');
    }
    #[Route('/request/{id}/accept', name: 'admin_family_event_accept_request', methods: ['POST'])]
    public function acceptRequest(
        \App\Entity\FamilyEventRequest $request,
        EntityManagerInterface $em
    ): Response {
        $request->setStatus(\App\Entity\FamilyEventRequest::STATUS_ACCEPTED);
        
        // Ajouter la famille à l'événement
        $event = $request->getEvent();
        $family = $request->getFamily();
        
        if (!$event->getAssignedFamilies()->contains($family)) {
            $event->addAssignedFamily($family);
        }

        $em->flush();
        $this->addFlash('success', 'La demande a été acceptée et la famille inscrite.');

        return $this->redirectToRoute('admin_family_event_requests', ['id' => $event->getId()]);
    }

    #[Route('/request/{id}/refuse', name: 'admin_family_event_refuse_request', methods: ['POST'])]
    public function refuseRequest(
        \App\Entity\FamilyEventRequest $request,
        EntityManagerInterface $em
    ): Response {
        $request->setStatus(\App\Entity\FamilyEventRequest::STATUS_REFUSED);
        
        // Si la famille était inscrite, on la retire ? (Optionnel, ici on gère juste la demande)
        // $request->getEvent()->removeAssignedFamily($request->getFamily());

        $em->flush();
        $this->addFlash('warning', 'La demande a été refusée.');

        return $this->redirectToRoute('admin_family_event_requests', ['id' => $request->getEvent()->getId()]);
    }
}
