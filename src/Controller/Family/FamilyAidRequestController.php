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
        $aidRequest = $repo->findOneBy(['family' => $this->getUser()]);

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
             $aidRequest->setIsUpdated(true);  // ton champ bool
            $aidRequest->setStatus(AidRequestStatus::PENDING);  // statut = En attente


            $em->flush();

            return $this->redirectToRoute('app_aidrequest_success');
        }

        return $this->render('family/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
