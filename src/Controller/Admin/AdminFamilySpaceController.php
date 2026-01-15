<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/family-space')]
final class AdminFamilySpaceController extends AbstractController
{
    #[Route(name: 'app_admin_family_space')]
    public function index(): Response
    {
        return $this->render('admin/family_space/index.html.twig');
    }
}
