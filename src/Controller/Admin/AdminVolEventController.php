<?php

namespace App\Controller\Admin;

use App\Entity\VolunteerEvent;
use App\Form\VolunteerEventType;
use App\Repository\VolunteerEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/volunteer/event')]
final class AdminVolEventController extends AbstractController
{
    #[Route('/choose', name: 'app_volunteer_event_choose', methods: ['GET'])]
    public function choose(): Response
    {
        return $this->render('volunteer_event/choose.html.twig');
    }

    #[Route(name: 'app_volunteer_event_index', methods: ['GET'])]
    public function index(VolunteerEventRepository $volunteerEventRepository): Response
    {
        return $this->render('volunteer_event/index.html.twig', [
            'volunteer_events' => $volunteerEventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_volunteer_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $volunteerEvent = new VolunteerEvent();
        $form = $this->createForm(VolunteerEventType::class, $volunteerEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($volunteerEvent);
            $entityManager->flush();

            return $this->redirectToRoute('app_volunteer_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('volunteer_event/new.html.twig', [
            'volunteer_event' => $volunteerEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_volunteer_event_show', methods: ['GET'])]
    public function show(VolunteerEvent $volunteerEvent): Response
    {
        return $this->render('volunteer_event/show.html.twig', [
            'volunteer_event' => $volunteerEvent,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_volunteer_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, VolunteerEvent $volunteerEvent, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VolunteerEventType::class, $volunteerEvent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_volunteer_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('volunteer_event/edit.html.twig', [
            'volunteer_event' => $volunteerEvent,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_volunteer_event_delete', methods: ['POST'])]
    public function delete(Request $request, VolunteerEvent $volunteerEvent, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$volunteerEvent->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($volunteerEvent);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_volunteer_event_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/requests', name: 'app_volunteer_event_requests', methods: ['GET'])]
    public function requests(VolunteerEvent $volunteerEvent): Response
    {
        return $this->render('volunteer_event/requests.html.twig', [
            'volunteer_event' => $volunteerEvent,
        ]);
    }

    #[Route('/request/{id}/{status}', name: 'app_volunteer_event_handle_request', methods: ['POST', 'GET'])]
    public function handleRequest(\App\Entity\VolunteerEventRequest $request, string $status, EntityManagerInterface $entityManager): Response
    {
        if (!in_array($status, [\App\Entity\VolunteerEventRequest::STATUS_ACCEPTED, \App\Entity\VolunteerEventRequest::STATUS_REFUSED])) {
            throw $this->createNotFoundException('Invalid status');
        }

        $request->setStatus($status);
        $event = $request->getEvent();

        if ($status === \App\Entity\VolunteerEventRequest::STATUS_ACCEPTED) {
            $event->addAssignedVolunteer($request->getVolunteer());
        } elseif ($status === \App\Entity\VolunteerEventRequest::STATUS_REFUSED) {
            $event->removeAssignedVolunteer($request->getVolunteer());
        }

        $entityManager->flush();

        $this->addFlash('success', 'La demande a été mise à jour.');

        return $this->redirectToRoute('app_volunteer_event_requests', ['id' => $event->getId()]);
    }
}
