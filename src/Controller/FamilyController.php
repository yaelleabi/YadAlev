<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AidRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AidRequestType;

final class FamilyController extends AbstractController
{
    #[Route('/family', name: 'app_family')]
    public function index(): Response
    {
        return $this->render('family/index.html.twig');
    }

    #[Route('/family/info', name: 'app_family_info')]
    public function info(
        AidRequestRepository $aidRequestRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // L’utilisateur connecté EST la famille
        $family = $this->getUser();

        if (!$family) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        // On récupère SA demande d’aide
        $aidRequest = $aidRequestRepository->findOneBy([
            'family' => $family
        ]);

        if (!$aidRequest) {
            return $this->redirectToRoute('app_family');
        }

        // Formulaire non-modifiable des infos (Affichage uniquement)
        $form = $this->createForm(AidRequestType::class, $aidRequest, [
            'is_family' => true, // identité désactivée
        ]);

        $form->handleRequest($request);

        // Soumission depuis la page INFO (si jamais tu l’actives plus tard)
        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des fichiers si ajout
            $fileFields = [
                'identityProofFilename',
                'incomeProofFilename',
                'taxNoticeFilename',
                'quittanceLoyer',
                'avisCharge',
                'taxeFonciere',
                'fraisScolarite',
                'attestationCaf',
                'otherDocumentFilename'
            ];

            foreach ($fileFields as $field) {
                $uploadedFile = $form->get($field)->getData();

                if ($uploadedFile) {
                    $filename = uniqid() . '-' . $uploadedFile->getClientOriginalName();
                    $uploadedFile->move('uploads', $filename);

                    $setter = 'set' . ucfirst($field);
                    $aidRequest->$setter($filename);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour.');

            return $this->redirectToRoute('app_family_info');
        }

        return $this->render('family/info.html.twig', [
            'family' => $family,
            'aidRequest' => $aidRequest,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/family/info/edit', name: 'app_family_info_edit')]
    public function edit(
        AidRequestRepository $aidRequestRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $family = $this->getUser();

        if (!$family) {
            throw $this->createAccessDeniedException("Vous devez être connecté.");
        }

        $aidRequest = $aidRequestRepository->findOneBy([
            'family' => $family
        ]);

        if (!$aidRequest) {
            return $this->redirectToRoute('app_family');
        }

        // Formulaire édition — identité désactivée
        $form = $this->createForm(AidRequestType::class, $aidRequest, [
            'is_family' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion des fichiers
            $fileFields = [
                'identityProofFilename',
                'incomeProofFilename',
                'taxNoticeFilename',
                'quittanceLoyer',
                'avisCharge',
                'taxeFonciere',
                'fraisScolarite',
                'attestationCaf',
                'otherDocumentFilename'
            ];

            foreach ($fileFields as $field) {
                $uploadedFile = $form->get($field)->getData();

                if ($uploadedFile) {
                    $filename = uniqid() . '-' . $uploadedFile->getClientOriginalName();
                    $uploadedFile->move('uploads', $filename);

                    $setter = 'set' . ucfirst($field);
                    $aidRequest->$setter($filename);
                }
            }

            $em->flush();

            // 👉 On ré-affiche la page d’édition AVEC le toast
            return $this->render('family/edit.html.twig', [
                'form' => $form->createView(),
                'aidRequest' => $aidRequest,
                'showToast' => true,
            ]);
        }

        // Affichage initial de la page (sans toast)
        return $this->render('family/edit.html.twig', [
            'form' => $form->createView(),
            'aidRequest' => $aidRequest,
            'showToast' => false,
        ]);
    }

}
