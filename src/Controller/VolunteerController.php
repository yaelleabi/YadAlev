<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VolunteerController extends AbstractController
{
    #[Route('/volunteer', name: 'app_volunteer')]
    public function index(\App\Repository\VolunteerEventRepository $eventRepo, \App\Repository\VolunteerEventRequestRepository $requestRepo): Response
    {
        /** @var \App\Entity\Volunteer $volunteer */
        $volunteer = $this->getUser();
        $now = new \DateTime();

        // 1. Missions Réalisées (Passées)
        $completedMissions = 0;
        // 2. Missions à Venir (Futures/En cours)
        $upcomingMissions = 0;

        foreach ($volunteer->getVolunteerEvents() as $event) {
            if ($event->getEndDate() < $now) {
                $completedMissions++;
            } else {
                $upcomingMissions++;
            }
        }

        // 3. Demandes en attente
        $pendingRequests = $requestRepo->count([
            'volunteer' => $volunteer,
            'status' => \App\Entity\VolunteerEventRequest::STATUS_PENDING
        ]);

        // 4. Nouvelles Opportunités (Total visible future events - (Assigned + Pending + Refused?))
        // Simplification: Count all future visible events, subtract those where user is involved
        $allFutureEventsKeyed = [];
        foreach ($eventRepo->findBy(['isVisible' => true], ['startDate' => 'ASC']) as $evt) {
            if ($evt->getEndDate() > $now) {
                $allFutureEventsKeyed[$evt->getId()] = true;
            }
        }
        $totalFutureEvents = count($allFutureEventsKeyed);

        // Count events where user is involved (Assigned or Request made)
        $involvedEventsCount = 0;
        
        // Check assigned (already counted in upcoming, but need to check intersection with future visible)
        foreach ($volunteer->getVolunteerEvents() as $event) {
             if (isset($allFutureEventsKeyed[$event->getId()])) {
                 $involvedEventsCount++;
                 // Remove to avoid double counting if logic gets complex, but here simplistic approach
                 unset($allFutureEventsKeyed[$event->getId()]);
             }
        }

        // Check requests (Pending/Refused/Accepted - Accepted is usually same as assigned but check purely requests just in case)
        $userRequests = $requestRepo->findBy(['volunteer' => $volunteer]);
        foreach ($userRequests as $req) {
            if (isset($allFutureEventsKeyed[$req->getEvent()->getId()])) {
                 $involvedEventsCount++;
                 unset($allFutureEventsKeyed[$req->getEvent()->getId()]);
            }
        }

        // Remaining in array are new opportunities
        $newOpportunities = count($allFutureEventsKeyed);


        return $this->render('volunteer/index.html.twig', [
            'volunteer' => $volunteer,
            'completedMissions' => $completedMissions,
            'upcomingMissions' => $upcomingMissions,
            'pendingRequests' => $pendingRequests,
            'newOpportunities' => $newOpportunities,
        ]);
    }
}
