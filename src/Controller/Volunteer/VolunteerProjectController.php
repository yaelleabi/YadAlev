<?php

namespace App\Controller\Volunteer;

use App\Entity\Volunteer;
use App\Repository\VolunteerEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_VOLUNTEER')]
#[Route('/volunteer/projects')]
final class VolunteerProjectController extends AbstractController
{
    #[Route('/', name: 'app_volunteer_project_index', methods: ['GET'])]
    public function index(VolunteerEventRepository $repository): Response
    {
        $events = $repository->findBy(['isVisible' => true], ['startDate' => 'ASC']);

        /** @var Volunteer $volunteer */
        $volunteer = $this->getUser();
        $myRequests = [];

        if ($volunteer instanceof Volunteer) {
            foreach ($volunteer->getVolunteerEventRequests() as $req) {
                $myRequests[$req->getEvent()->getId()] = $req;
            }
        }

        return $this->render('volunteer/project/index.html.twig', [
            'events' => $events,
            'myRequests' => $myRequests,
        ]);
    }

    #[Route('/register/{id}', name: 'app_volunteer_project_register', methods: ['POST'])]
    public function register(\App\Entity\VolunteerEvent $event, EntityManagerInterface $em): Response
    {
        /** @var Volunteer $volunteer */
        $volunteer = $this->getUser();

        if (!$volunteer instanceof Volunteer) {
            return $this->redirectToRoute('app_home');
        }

        $existingRequest = $em->getRepository(\App\Entity\VolunteerEventRequest::class)->findOneBy([
            'volunteer' => $volunteer,
            'event' => $event
        ]);

        if ($existingRequest) {
            $this->addFlash('warning', 'Vous avez déjà une demande pour cette mission.');
        } else {
            $request = new \App\Entity\VolunteerEventRequest();
            $request->setVolunteer($volunteer);
            $request->setEvent($event);
            $request->setStatus(\App\Entity\VolunteerEventRequest::STATUS_PENDING);

            $em->persist($request);
            $em->flush();

            $this->addFlash('success', 'Votre demande de participation a été envoyée.');
        }

        return $this->redirectToRoute('app_volunteer_project_index');
    }
}
