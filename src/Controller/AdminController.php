<?php

namespace App\Controller;

use App\Repository\AidRequestRepository;
use App\Repository\FamilyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * Page d’accueil de l’espace admin
     * Route : app_admin
     */
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig');
    }

    /**
     * Créer un projet d’aide
     * Route : app_admin_aidproject_new
     */
    #[Route('/admin/aidproject/new', name: 'app_admin_aidproject_new')]
    public function createAidProject(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/aidproject_new.html.twig');
    }

    /**
     * Gérer les demandes des familles
     * Route : app_admin_aidrequest_list
     */
    #[Route('/admin/aidrequests', name: 'app_admin_aidrequest_list')]
    public function listAidRequests(AidRequestRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

         return $this->render('aid_request/index.html.twig', [
            'aid_requests' => $repo->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

    /**
     * Profil / liste des familles
     * Route : app_admin_family_list
     */
    #[Route('/admin/families', name: 'app_admin_family_list')]
    public function listFamilies(FamilyRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/family_list.html.twig', [
            'families' => $repo->findAll()
        ]);
    }
}
