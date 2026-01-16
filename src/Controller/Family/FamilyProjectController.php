<?php

namespace App\Controller\Family;

use App\Entity\Family;
use App\Entity\FamilyEvent;
use App\Repository\FamilyEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(attribute: 'ROLE_FAMILY')]
#[Route('/family/projects')]
final class FamilyProjectController extends AbstractController
{
    #[Route('/', name: 'app_family_project_index', methods: ['GET'])]
    public function index(FamilyEventRepository $repository): Response
    {
        // Récupérer uniquement les événements visibles
        $events = $repository->findBy(['isVisible' => true], ['startDate' => 'ASC']);

        // Récupérer les demandes de la famille connectée pour affichage du statut
        $family = $this->getUser();
        $myRequests = [];
        $isProfileComplete = false;
        
        if ($family instanceof Family) {
            foreach ($family->getFamilyEventRequests() as $req) {
                // On indexe par l'ID de l'événement pour accès facile dans Twig
                $myRequests[$req->getEvent()->getId()] = $req;
            }

            // Vérification des infos obligatoires
            if (
                $family->getFirstName() && 
                $family->getPhoneNumber() &&
                $family->getDateOfBirth() &&
                $family->getAdress() && 
                $family->getAdress()->getCity() && 
                $family->getAdress()->getPostalCode()
            ) {
                $isProfileComplete = true;
            }
        }

        return $this->render('family/project/index.html.twig', [
            'events' => $events,
            'myRequests' => $myRequests,
            'isProfileComplete' => $isProfileComplete
        ]);
    }

    #[Route('/register/{id}', name: 'app_family_project_register', methods: ['POST'])]
    public function register(FamilyEvent $event, EntityManagerInterface $em): Response
    {
        /** @var Family $family */
        $family = $this->getUser();

        if (!$family instanceof Family) {
            return $this->redirectToRoute('app_home');
        }

        // Vérifier si une demande existe déjà (en attente ou acceptée)
        $existingRequest = $em->getRepository(\App\Entity\FamilyEventRequest::class)->findOneBy([
            'family' => $family,
            'event' => $event
        ]);

        if ($existingRequest) {
            $this->addFlash('warning', 'Vous avez déjà une demande en cours ou traitée pour ce projet.');
        } else {
            $request = new \App\Entity\FamilyEventRequest();
            $request->setFamily($family);
            $request->setEvent($event);
            $request->setStatus(\App\Entity\FamilyEventRequest::STATUS_PENDING);
            
            $em->persist($request);
            $em->flush();
            
            $this->addFlash('success', 'Votre demande a bien été envoyée. Elle est en attente de validation.');
        }

        return $this->redirectToRoute('app_family_project_index');
    }
}
