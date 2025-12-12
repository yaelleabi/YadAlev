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
use App\Enum\AidRequestStatus;
use App\Repository\AidRequestRepository;
use App\Entity\Family;
use App\Service\AidRequestMailer;
use App\Service\FamilySyncService;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(attribute: 'ROLE_FAMILY')]
final class FamilyAidRequestController extends AbstractController
{
    #[Route('/family/aid/request', name: 'app_family_aid_request')]
    public function index(): Response
    {
        return $this->render('family/family_aid_request/index.html.twig');
    }

    /* ========================== NEW AID REQUEST ========================== */

    #[Route('/family/aidrequest/new', name: 'app_aidrequest_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        AidRequestMailer $requestMailer,
        FamilySyncService $sync
    ): Response
    {
        /** @var Family $family */
        $family = $this->getUser();

        // S'il existe déjà une demande, redirection
        if ($em->getRepository(AidRequest::class)->findOneBy(['family' => $family])) {
            return $this->redirectToRoute('app_aidrequest_existing');
        }

        // Nouvelle demande pré-remplie avec Family
        $aidRequest = new AidRequest();
        $aidRequest->setFamily($family);
        $aidRequest->setStatus(AidRequestStatus::PENDING);

        // Pré-remplissage
        $sync->fillAidRequestFromFamily($family, $aidRequest);

        $form = $this->createForm(AidRequestType::class, $aidRequest, ['is_family' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload fichiers
            $this->handleUploads($form, $aidRequest, $slugger);

            // Mise à jour Family depuis la nouvelle demande
            $sync->updateFamilyFromAidRequest($family, $aidRequest);

            $em->persist($aidRequest);
            $em->flush();

            $requestMailer->sendAdminNotification($aidRequest);

            return $this->redirectToRoute('app_aidrequest_success');
        }

        return $this->render('family/family_aid_request/new.html.twig', [
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
        $family = $this->getUser();
        $aidRequest = $repo->findOneBy(['family' => $family], ['createdAt' => 'DESC']);

        return $aidRequest
            ? $this->render('family/family_aid_request/existing.html.twig', [
                'aid_request' => $aidRequest,
                'family' => $family,
            ])
            : $this->redirectToRoute('app_aidrequest_new');
    }

    #[Route('/family/aidrequest/{id}', name: 'app_aidrequest_show')]
    public function showFamily(AidRequest $aidRequest): Response
    {
        if ($aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('family/family_aid_request/show.html.twig', [
            'aid_request' => $aidRequest,
        ]);
    }

    #[Route('/family/aidrequest/{id}/edit', name: 'app_aidrequest_edit')]
    public function editFamily(
        Request $request,
        AidRequest $aidRequest,
        EntityManagerInterface $em,
        FamilySyncService $sync
    ): Response
    {
        if ($aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** @var Family $family */
        $family = $this->getUser();

        $form = $this->createForm(AidRequestType::class, $aidRequest, ['is_family' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Family doit être synchronisé depuis l'AidRequest
            $sync->updateFamilyFromAidRequest($family, $aidRequest);

            $em->flush();

            return $this->redirectToRoute('app_aidrequest_show', ['id' => $aidRequest->getId()]);
        }

        return $this->render('family/family_aid_request/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /* ============================== RENEW ============================== */

    #[Route('/family/aidrequest/{id}/renew', name: 'app_aidrequest_renew')]
    public function renew(AidRequest $oldRequest, FamilySyncService $sync): Response
    {
        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** @var Family $family */
        $family = $this->getUser();

        // Nouvelle demande basée sur Family
        $new = new AidRequest();
        $new->setFamily($family);
        $new->setStatus(AidRequestStatus::PENDING);

        $sync->fillAidRequestFromFamily($family, $new);

        $form = $this->createForm(AidRequestType::class, $new, ['is_family' => true]);

        return $this->render('family/family_aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest,
        ]);
    }

    #[Route('/family/aidrequest/{id}/renew-submit', name: 'app_aidrequest_renew_submit')]
    public function renewSubmit(
        Request $request,
        AidRequest $oldRequest,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        AidRequestMailer $requestMailer,
        FamilySyncService $sync
    ): Response
    {
        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** @var Family $family */
        $family = $this->getUser();

        // Nouvelle demande
        $new = new AidRequest();
        $new->setFamily($family);
        $new->setStatus(AidRequestStatus::PENDING);

        $sync->fillAidRequestFromFamily($family, $new);

        $form = $this->createForm(AidRequestType::class, $new, ['is_family' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->handleUploads($form, $new, $slugger);

            // MAJ FAMILY depuis cette nouvelle demande — TRES IMPORTANT
            $sync->updateFamilyFromAidRequest($family, $new);

            $em->persist($new);
            $em->flush();

            $requestMailer->sendAdminNotification($new);

            $this->addFlash('success', 'Votre demande de renouvellement a bien été enregistrée.');
            return $this->redirectToRoute('app_aidrequest_success');
        }

        return $this->render('aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest,
        ]);
    }

    /* ============================== PRIVATE HELPERS ============================== */

    private function handleUploads($form, AidRequest $request, SluggerInterface $slugger): void
    {
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

        $fields = [
            'identityProofFilename', 'incomeProofFilename', 'taxNoticeFilename',
            'quittanceLoyer', 'avisCharge', 'taxeFonciere',
            'fraisScolarite', 'attestationCaf', 'otherDocumentFilename'
        ];

        foreach ($fields as $field) {
            $file = $form->get($field)->getData();

            if ($file) {
                $newFilename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '-' . uniqid() . '.' . $file->guessExtension();

                $file->move($uploadDir, $newFilename);

                $setter = 'set' . ucfirst($field);
                $request->$setter($newFilename);
            }
        }
    }
}
