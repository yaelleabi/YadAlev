<?php

namespace App\Controller\Family;

use App\Entity\Family;
use App\Form\FamilyType;
use App\Repository\FamilyRepository;
use App\Repository\AidRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\AidRequest;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(attribute: 'ROLE_FAMILY')]
#[Route('/family')]
final class FamilyController extends AbstractController
{
    #[Route('/home', name: 'app_family_home')]
    public function home(AidRequestRepository $repo, \App\Repository\AnnouncementRepository $announcementRepo): Response
    {
        $aidRequest = $repo->findOneBy(['family' => $this->getUser()]);
        $announcement = $announcementRepo->findOneBy(['isActive' => true], ['createdAt' => 'DESC']);

        return $this->render('family/home.html.twig', [
            'aidRequest' => $aidRequest,
            'family' => $this->getUser(),
            'announcement' => $announcement
        ]);
    }

    #[Route('/calendly', name: 'app_calendly')]
    public function calendly(): Response
    {
        return $this->render('family/calendly.html.twig');
    }

    #[Route('/new', name: 'app_family_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $family = new Family();
        $form = $this->createForm(FamilyType::class, $family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($family);
            $entityManager->flush();

            return $this->redirectToRoute('app_family_index');
        }

        return $this->render('family/new.html.twig', [
            'family' => $family,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_family_show', methods: ['GET'])]
    public function show(
        Family $family,
        AidRequestRepository $aidRequestRepository
    ): Response {
        
        // Récupérer toutes les demandes validées de cette famille
        $validatedRequests = $aidRequestRepository->findValidatedByFamily($family);

        return $this->render('family/show.html.twig', [
            'family' => $family,
            'validatedRequests' => $validatedRequests
        ]);
    }


    #[Route('/family/info/edit', name: 'app_family_edit', methods:['GET','POST'])]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $family = $this->getUser();

        if (!$family instanceof Family) {
            throw new \Exception("L'utilisateur connecté n'est pas une famille !");
        }

        $lastRequest = !$family->getAidRequests()->isEmpty()
        ? $family->getAidRequests()->last()
        : null;

        // 🔥 AUTO-PRE-REMPLISSAGE UNE SEULE FOIS
        if ($lastRequest && !$family->getFirstName()) {

            $family->setFirstName($lastRequest->getFirstName());
            $family->setDateOfBirth($lastRequest->getDateOfBirth());
            $family->setAdress($lastRequest->getAdress());

            $family->setHousingStatus($lastRequest->getHousingStatus());
            $family->setMaritalStatus($lastRequest->getMaritalStatus());
            $family->setDependantsCount($lastRequest->getDependantsCount());

            $family->setEmploymentStatus($lastRequest->getEmploymentStatus());
            $family->setMonthlyIncome($lastRequest->getMonthlyIncome());
            $family->setSpouseEmploymentStatus($lastRequest->getSpouseEmploymentStatus());
            $family->setSpouseMonthlyIncome($lastRequest->getSpouseMonthlyIncome());

            $family->setFamilyAllowanceAmount($lastRequest->getFamilyAllowanceAmount());
            $family->setAlimonyAmount($lastRequest->getAlimonyAmount());
            $family->setRentAmountNetAide($lastRequest->getRentAmountNetAide());

            $family->setOtherNeed($lastRequest->getOtherNeed());
            $family->setOtherComment($lastRequest->getOtherComment());

            $em->flush(); // 💾 Family reçoit les données une fois
        }

        $form = $this->createForm(FamilyType::class, $family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash("success", "Vos informations ont bien été mises à jour.");
            return $this->redirectToRoute('app_family_edit');
        }

        return $this->render('family/edit.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_family_delete', methods: ['POST'])]
    public function delete(Request $request, Family $family, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$family->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($family);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_family_index');
    }
}
