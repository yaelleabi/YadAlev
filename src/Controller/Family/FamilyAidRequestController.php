<?php

namespace App\Controller\Family;

use App\Entity\AidRequest;
use App\Form\AidRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Enum\AidRequestStatus;
use App\Repository\AidRequestRepository;

final class FamilyAidRequestController extends AbstractController
{
    #[Route('/family/aid/request', name: 'app_family_aid_request')]
    public function index(): Response
    {
        return $this->render('family_aid_request/index.html.twig', [
            'controller_name' => 'FamilyAidRequestController',
        ]);
    }
      #[Route('/family/aidrequest/new', name: 'app_aidrequest_new')]
     public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
        {
             $user = $this->getUser();

            // Vérifier si une demande existe déjà
            $existing = $em->getRepository(AidRequest::class)->findOneBy([
                'family' => $user
            ]);

            if ($existing) {
                return $this->redirectToRoute('app_aidrequest_existing');
            }

            // --- Création de la nouvelle demande ---
            $aidRequest = new AidRequest();
            $aidRequest->setFamily($user);
            $aidRequest->setStatus(AidRequestStatus::PENDING);

            // Préremplissage
            if ($user) {
                if (method_exists($user, 'getName')) {
                    $aidRequest->setLastName($user->getName());
                }
                if (method_exists($user, 'getEmail')) {
                    $aidRequest->setEmail($user->getEmail());
                }
                if (method_exists($user, 'getPhoneNumber')) {
                    $aidRequest->setPhoneNumber($user->getPhoneNumber());
                }
            }

            $form = $this->createForm(AidRequestType::class, $aidRequest, [
                'is_family' => true,
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                // Uploads
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $fileFields = [
                    'identityProofFilename', 'incomeProofFilename', 'taxNoticeFilename',
                    'quittanceLoyer', 'avisCharge', 'taxeFonciere', 'fraisScolarite',
                    'attestationCaf', 'otherDocumentFilename'
                ];

                foreach ($fileFields as $field) {
                    $file = $form->get($field)->getData();
                    if ($file) {
                        $newFilename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            . '-' . uniqid() . '.' . $file->guessExtension();

                        try {
                            $file->move($uploadDir, $newFilename);
                        } catch (FileException $e) {}

                        $setter = 'set' . ucfirst($field);
                        if (method_exists($aidRequest, $setter)) {
                            $aidRequest->$setter($newFilename);
                        }
                    }
                }

                $em->persist($aidRequest);
                $em->flush();

                return $this->redirectToRoute('app_aidrequest_success');
            }

            return $this->render('aid_request/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    #[Route('/family/aidrequest/success', name: 'app_aidrequest_success')]
    public function success(): Response
    {
        return $this->render('aid_request/success.html.twig');
    }
    #[Route('/family/aidrequest/existing', name: 'app_aidrequest_existing')]
    public function existing(AidRequestRepository $repo): Response
    {
        $aidRequest = $repo->findOneBy(
    ['family' => $this->getUser()],
    ['createdAt' => 'DESC']);

        if (!$aidRequest) {
            return $this->redirectToRoute('app_aidrequest_new');
        }

        return $this->render('aid_request/existing.html.twig', [
            'aid_request' => $aidRequest,
        ]);
    }

    /* ============================================================
        FAMILLE – AFFICHAGE DÉTAILLÉ
    ============================================================ */
    #[Route('/family/aidrequest/{id}', name: 'app_aidrequest_show')]
    public function showFamily(AidRequest $aidRequest): Response
    {
        if ($aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('family_aid_request/show.html.twig', [
            'aid_request' => $aidRequest,
        ]);
    }
     #[Route('/family/aidrequest/{id}/edit', name: 'app_aidrequest_edit')]
    public function editFamily(Request $request, AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        if ($aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AidRequestType::class, $aidRequest, [
            'is_family' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Votre demande a bien été mise à jour.');
            return $this->redirectToRoute('app_aidrequest_show', ['id' => $aidRequest->getId()]);
        }

        return $this->render('family_aid_request/edit.html.twig', [
            'form' => $form->createView(),
            'aid_request' => $aidRequest,
        ]);
    }
    #[Route('/family/aidrequest/{id}/renew', name: 'app_aidrequest_renew')]
    public function renew(
        AidRequest $oldRequest,
        EntityManagerInterface $em
    ): Response {

        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Nouvelle demande pré-remplie
        $new = new AidRequest();
        $new->setFamily($this->getUser());
        $new->setCreatedAt(new \DateTimeImmutable());
        $new->setStatus(AidRequestStatus::PENDING);
        $new->setIsUpdated(false);

        // Copie des données (exactement comme dans renewSubmit)
        $new->setLastName($oldRequest->getLastName());
        $new->setFirstName($oldRequest->getFirstName());
        $new->setDateOfBirth($oldRequest->getDateOfBirth());
        $new->setEmail($oldRequest->getEmail());
        $new->setPhoneNumber($oldRequest->getPhoneNumber());
        $new->setAdress(clone $oldRequest->getAdress());

        $new->setHousingStatus($oldRequest->getHousingStatus());
        $new->setMaritalStatus($oldRequest->getMaritalStatus());
        $new->setDependantsCount($oldRequest->getDependantsCount());
        $new->setEmploymentStatus($oldRequest->getEmploymentStatus());
        $new->setMonthlyIncome($oldRequest->getMonthlyIncome());
        $new->setSpouseEmploymentStatus($oldRequest->getSpouseEmploymentStatus());
        $new->setSpouseMonthlyIncome($oldRequest->getSpouseMonthlyIncome());
        $new->setFamilyAllowanceAmount($oldRequest->getFamilyAllowanceAmount());
        $new->setAlimonyAmount($oldRequest->getAlimonyAmount());
        $new->setRentAmountNetAide($oldRequest->getRentAmountNetAide());

        $new->setRequestType($oldRequest->getRequestType());
        $new->setRequestDuration($oldRequest->getRequestDuration());
        $new->setOtherRequestDuration($oldRequest->getOtherRequestDuration());
        $new->setRequestReason($oldRequest->getRequestReason());
        $new->setUrgencyExplanation($oldRequest->getUrgencyExplanation());
        $new->setUrgencyLevel($oldRequest->getUrgencyLevel());
        $new->setOtherNeed($oldRequest->getOtherNeed());
        $new->setOtherComment($oldRequest->getOtherComment());

        $new->setPrivacyConsent($oldRequest->getPrivacyConsent());

        // Préremplir les noms des fichiers
        $new->setIdentityProofFilename($oldRequest->getIdentityProofFilename());
        $new->setIncomeProofFilename($oldRequest->getIncomeProofFilename());
        $new->setTaxNoticeFilename($oldRequest->getTaxNoticeFilename());
        $new->setOtherDocumentFilename($oldRequest->getOtherDocumentFilename());
        $new->setQuittanceLoyer($oldRequest->getQuittanceLoyer());
        $new->setAvisCharge($oldRequest->getAvisCharge());
        $new->setTaxeFonciere($oldRequest->getTaxeFonciere());
        $new->setFraisScolarite($oldRequest->getFraisScolarite());
        $new->setAttestationCaf($oldRequest->getAttestationCaf());

        // Formulaire
        $form = $this->createForm(AidRequestType::class, $new, [
            'is_family' => true,
            'action' => $this->generateUrl('app_aidrequest_renew_submit', ['id' => $oldRequest->getId()]),
            'method' => 'POST'
        ]);

        return $this->render('aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest,
        ]);
    }

    #[Route('/family/aidrequest/{id}/renew/form', name: 'app_aidrequest_renew_form')]
    public function renewForm(AidRequest $oldRequest): Response
    {
        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // On crée un objet temporaire NON persisté
        $draft = clone $oldRequest;
        $draft->setStatus(AidRequestStatus::PENDING);

        // Formulaire
        $form = $this->createForm(AidRequestType::class, $draft, [
            'is_family' => true,
        ]);

        return $this->render('aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest
        ]);
    }
    #[Route('/family/aidrequest/{id}/renew/submit', name: 'app_aidrequest_renew_submit')]
    public function renewSubmit(
        Request $request,
        AidRequest $oldRequest,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {

        // Vérification de la famille
        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Nouvelle demande (vide au départ)
        $new = new AidRequest();
        $new->setFamily($this->getUser());
        $new->setCreatedAt(new \DateTimeImmutable());
        $new->setStatus(AidRequestStatus::PENDING);
        $new->setIsUpdated(false);

        // ⚠️ On préremplit AVANT création du formulaire
        // Identité
        $new->setLastName($oldRequest->getLastName());
        $new->setFirstName($oldRequest->getFirstName());
        $new->setDateOfBirth($oldRequest->getDateOfBirth());
        $new->setEmail($oldRequest->getEmail());
        $new->setPhoneNumber($oldRequest->getPhoneNumber());

        // Adresse
        $newAdress = clone $oldRequest->getAdress();
        $new->setAdress($newAdress);

        // Infos socio-financières
        $new->setHousingStatus($oldRequest->getHousingStatus());
        $new->setMaritalStatus($oldRequest->getMaritalStatus());
        $new->setDependantsCount($oldRequest->getDependantsCount());
        $new->setEmploymentStatus($oldRequest->getEmploymentStatus());
        $new->setMonthlyIncome($oldRequest->getMonthlyIncome());
        $new->setSpouseEmploymentStatus($oldRequest->getSpouseEmploymentStatus());
        $new->setSpouseMonthlyIncome($oldRequest->getSpouseMonthlyIncome());
        $new->setFamilyAllowanceAmount($oldRequest->getFamilyAllowanceAmount());
        $new->setAlimonyAmount($oldRequest->getAlimonyAmount());
        $new->setRentAmountNetAide($oldRequest->getRentAmountNetAide());

        // Demande
        $new->setRequestType($oldRequest->getRequestType());
        $new->setRequestDuration($oldRequest->getRequestDuration());
        $new->setOtherRequestDuration($oldRequest->getOtherRequestDuration());
        $new->setRequestReason($oldRequest->getRequestReason());
        $new->setUrgencyExplanation($oldRequest->getUrgencyExplanation());
        $new->setUrgencyLevel($oldRequest->getUrgencyLevel());
        $new->setOtherNeed($oldRequest->getOtherNeed());
        $new->setOtherComment($oldRequest->getOtherComment());
        $new->setPrivacyConsent($oldRequest->getPrivacyConsent());

        // Documents (uniquement les noms → re-upload possible)
        $new->setIdentityProofFilename($oldRequest->getIdentityProofFilename());
        $new->setIncomeProofFilename($oldRequest->getIncomeProofFilename());
        $new->setTaxNoticeFilename($oldRequest->getTaxNoticeFilename());
        $new->setOtherDocumentFilename($oldRequest->getOtherDocumentFilename());
        $new->setQuittanceLoyer($oldRequest->getQuittanceLoyer());
        $new->setAvisCharge($oldRequest->getAvisCharge());
        $new->setTaxeFonciere($oldRequest->getTaxeFonciere());
        $new->setFraisScolarite($oldRequest->getFraisScolarite());
        $new->setAttestationCaf($oldRequest->getAttestationCaf());

        // ---------------------
        // FORMULAIRE
        // ---------------------
        $form = $this->createForm(AidRequestType::class, $new, [
            'is_family' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ----------------------------
            //  UPLOAD DES NOUVEAUX FICHIERS
            // ----------------------------
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

            $fileFields = [
                'identityProofFilename', 'incomeProofFilename', 'taxNoticeFilename',
                'quittanceLoyer', 'avisCharge', 'taxeFonciere',
                'fraisScolarite', 'attestationCaf', 'otherDocumentFilename'
            ];

            foreach ($fileFields as $field) {
                $file = $form->get($field)->getData();

                if ($file) {
                    $newFilename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        . '-' . uniqid() . '.' . $file->guessExtension();

                    try {
                        $file->move($uploadDir, $newFilename);
                    } catch (FileException $e) {}

                    $setter = 'set' . ucfirst($field);
                    if (method_exists($new, $setter)) {
                        $new->$setter($newFilename);
                    }
                }
            }

            // -------------------
            // SAUVEGARDE
            // -------------------
            $em->persist($new);
            $em->flush();

            return $this->redirectToRoute('app_aidrequest_success');
        }

        // -------------------
        // RÉAFFICHAGE DU FORMULAIRE
        // -------------------
        return $this->render('aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest,
        ]);
    }


   
}

    // #[Route('/family/info', name: 'app_family_info')]
    // public function info(
    //     AidRequestRepository $aidRequestRepository,
    //     Request $request,
    //     EntityManagerInterface $em
    // ): Response {
    //     // L’utilisateur connecté EST la famille
    //     $family = $this->getUser();

    //     if (!$family) {
    //         throw $this->createAccessDeniedException("Vous devez être connecté.");
    //     }

    //     // On récupère SA demande d’aide
    //     $aidRequest = $aidRequestRepository->findOneBy([
    //         'family' => $family
    //     ]);

    //     if (!$aidRequest) {
    //         return $this->redirectToRoute('app_family');
    //     }

    //     // Formulaire non-modifiable des infos (Affichage uniquement)
    //     $form = $this->createForm(AidRequestType::class, $aidRequest, [
    //         'is_family' => true, // identité désactivée
    //     ]);

    //     $form->handleRequest($request);

    //     // Soumission depuis la page INFO (si jamais tu l’actives plus tard)
    //     if ($form->isSubmitted() && $form->isValid()) {

    //         // Gestion des fichiers si ajout
    //         $fileFields = [
    //             'identityProofFilename',
    //             'incomeProofFilename',
    //             'taxNoticeFilename',
    //             'quittanceLoyer',
    //             'avisCharge',
    //             'taxeFonciere',
    //             'fraisScolarite',
    //             'attestationCaf',
    //             'otherDocumentFilename'
    //         ];

    //         foreach ($fileFields as $field) {
    //             $uploadedFile = $form->get($field)->getData();

    //             if ($uploadedFile) {
    //                 $filename = uniqid() . '-' . $uploadedFile->getClientOriginalName();
    //                 $uploadedFile->move('uploads', $filename);

    //                 $setter = 'set' . ucfirst($field);
    //                 $aidRequest->$setter($filename);
    //             }
    //         }

    //         $em->flush();

    //         $this->addFlash('success', 'Vos informations ont été mises à jour.');

    //         return $this->redirectToRoute('app_family_info');
    //     }

    //     return $this->render('family/info.html.twig', [
    //         'family' => $family,
    //         'aidRequest' => $aidRequest,
    //         'form' => $form->createView(),
    //     ]);
    // }
    // #[Route('/family/info/edit', name: 'app_family_info_edit')]
    // public function edit(
    //     AidRequestRepository $aidRequestRepository,
    //     Request $request,
    //     EntityManagerInterface $em
    // ): Response {
    //     $family = $this->getUser();

    //     if (!$family) {
    //         throw $this->createAccessDeniedException("Vous devez être connecté.");
    //     }

    //     $aidRequest = $aidRequestRepository->findOneBy([
    //         'family' => $family
    //     ]);

    //     if (!$aidRequest) {
    //         return $this->redirectToRoute('app_family');
    //     }

    //     // Formulaire édition — identité désactivée
    //     $form = $this->createForm(AidRequestType::class, $aidRequest, [
    //         'is_family' => true,
    //     ]);

    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {

    //         // Gestion des fichiers
    //         $fileFields = [
    //             'identityProofFilename',
    //             'incomeProofFilename',
    //             'taxNoticeFilename',
    //             'quittanceLoyer',
    //             'avisCharge',
    //             'taxeFonciere',
    //             'fraisScolarite',
    //             'attestationCaf',
    //             'otherDocumentFilename'
    //         ];

    //         foreach ($fileFields as $field) {
    //             $uploadedFile = $form->get($field)->getData();

    //             if ($uploadedFile) {
    //                 $filename = uniqid() . '-' . $uploadedFile->getClientOriginalName();
    //                 $uploadedFile->move('uploads', $filename);

    //                 $setter = 'set' . ucfirst($field);
    //                 $aidRequest->$setter($filename);
    //             }
    //         }
    //          $aidRequest->setIsUpdated(true);  // ton champ booL

    //         $em->flush();

    //         return $this->redirectToRoute('app_aidrequest_success');
    //     }

    //     return $this->render('family/edit.html.twig', [
    //         'form' => $form->createView(),
    //     ]);
    // }


