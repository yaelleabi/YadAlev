<?php

namespace App\Controller\Admin;

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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Family;
use App\Service\FamilySyncService;

#[IsGranted('ROLE_ADMIN')]
final class AdminAidRequestController extends AbstractController
{
    #[Route('/admin/aidrequests', name: 'app_admin_aidrequest_list')]
   public function listAidRequests(Request $request, AidRequestRepository $repo): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Récupération des paramètres de filtre
    $year = $request->query->get('year');
    $status = $request->query->get('status');

    // Récupération des demandes filtrées
    $aidRequests = $repo->findFiltered($year, $status);

    // Récupération des années disponibles pour le filtre
    $years = $repo->findAvailableYears();

    return $this->render('admin/admin_aid_request/index.html.twig', [
        'aid_requests' => $aidRequests,
        'years' => $years
    ]);
}

    #[Route('/admin/aidrequest/{id}', name: 'app_admin_aidrequest_show')]
    public function showAdmin(AidRequest $aidRequest): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/admin_aid_request/show.html.twig', [
            'aid_request' => $aidRequest,
        ]);
    }

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

        return $this->render('admin/admin_aid_request/edit.html.twig', [
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
    #[Route('/admin/family/{id}/aidrequest/new', name: 'admin_aidrequest_new_for_family', methods: ['GET', 'POST'])]
    public function newForFamily(
        Request $request,
        Family $family,
        EntityManagerInterface $em,
        FamilySyncService $sync
    ): Response {
        $aidRequest = new AidRequest();
        $aidRequest->setFamily($family);
        $aidRequest->setStatus(AidRequestStatus::PENDING);

        // ✅ pré-remplissage depuis Family
        $sync->fillAidRequestFromFamily($family, $aidRequest);

        // 🔥 IMPORTANT : côté admin, tu peux réutiliser AidRequestType
        // ou faire un AdminAidRequestType plus léger si tu veux.
        $form = $this->createForm(AidRequestType::class, $aidRequest, [
            'is_family' => false, // si tu utilises cette option dans ton form
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($aidRequest);
            $em->flush();

            $this->addFlash('success', 'La demande a bien été créée pour cette famille.');
            return $this->redirectToRoute('app_admin_family_list');
        }

        return $this->render('admin/admin_aid_request/new.html.twig', [
            'form' => $form->createView(),
            'family' => $family,
        ]);
    }
}