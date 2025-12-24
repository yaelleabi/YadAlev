<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminEventController extends AbstractController
{
    #[Route('/admin/event', name: 'app_admin_event')]
    public function index(): Response
    {
        return $this->render('admin/admin_event/index.html.twig', [
            'controller_name' => 'AdminEventController',
        ]);
    }
}
