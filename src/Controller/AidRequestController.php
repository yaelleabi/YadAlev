<?php

namespace App\Controller;

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

class AidRequestController extends AbstractController
{
    /* ============================================================
        FAMILLE – CRÉATION D'UNE DEMANDE
    ============================================================ */
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
                    $aidRequest->setFirstName($user->getName());
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

        return $this->render('aid_request/show.html.twig', [
            'aid_request' => $aidRequest,
        ]);
    }

    /* ============================================================
        ADMIN – AFFICHAGE DÉTAILLÉ
    ============================================================ */
    #[Route('/admin/aidrequest/{id}', name: 'app_admin_aidrequest_show')]
    public function showAdmin(AidRequest $aidRequest): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('aid_request/show.html.twig', [
            'aid_request' => $aidRequest,
        ]);
    }

    /* ============================================================
        FAMILLE – MODIFICATION
    ============================================================ */
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

        return $this->render('aid_request/edit.html.twig', [
            'form' => $form->createView(),
            'aid_request' => $aidRequest,
        ]);
    }

    /* ============================================================
        ADMIN – MODIFICATION
    ============================================================ */
    #[Route('/admin/aidrequest/{id}/edit', name: 'app_admin_aidrequest_edit')]
    public function editAdmin(Request $request, AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(AidRequestType::class, $aidRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Demande modifiée avec succès.');
            return $this->redirectToRoute('app_admin_aidrequest_list');
        }

        return $this->render('aid_request/edit.html.twig', [
            'form' => $form->createView(),
            'aid_request' => $aidRequest,
        ]);
    }

    /* ============================================================
        SUPPRESSION (ADMIN UNIQUEMENT)
    ============================================================ */
    #[Route('/admin/aidrequest/{id}/delete', name: 'app_aidrequest_delete', methods: ['POST'])]
    public function delete(Request $request, AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$aidRequest->getId(), $request->request->get('_token'))) {
            $em->remove($aidRequest);
            $em->flush();
        }

        return $this->redirectToRoute('app_admin_aidrequest_list');
    }
    #[Route('/admin/aidrequest/{id}/approve', name: 'app_admin_aidrequest_approve', methods: ['POST'])]
    public function approve(AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $aidRequest->setStatus(AidRequestStatus::VALIDATED);
        $em->flush();

        $this->addFlash('success', 'Demande acceptée !');
        return $this->redirectToRoute('app_admin_aidrequest_show', ['id' => $aidRequest->getId()]);
    }

    #[Route('/admin/aidrequest/{id}/reject', name: 'app_admin_aidrequest_reject', methods: ['POST'])]
    public function reject(AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $aidRequest->setStatus(AidRequestStatus::REFUSED);
        $em->flush();

        $this->addFlash('danger', 'Demande rejetée.');
        return $this->redirectToRoute('app_admin_aidrequest_show', ['id' => $aidRequest->getId()]);
    }
    

}
