<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VolunteerController extends AbstractController
{
    #[Route('/volunteer', name: 'app_volunteer')]
    public function index(): Response
    {
        return $this->render('volunteer/index.html.twig', [
            'controller_name' => 'VolunteerController',
        ]);
    }
}
