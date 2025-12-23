<?php

namespace App\Controller\Admin;

use App\Entity\Volunteer;
use App\Form\VolunteerType;
use App\Repository\VolunteerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/volunteer')]
final class AdminVolunteerController extends AbstractController
{
    #[Route(name: 'app_admin_volunteer_index', methods: ['GET'])]
    public function index(VolunteerRepository $volunteerRepository): Response
    {
        return $this->render('admin_volunteer/index.html.twig', [
            'volunteers' => $volunteerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_volunteer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $volunteer = new Volunteer();
        $volunteer->setRoles(['ROLE_VOLUNTEER']);
        $volunteer->setIsVerified(true);


        $form = $this->createForm(VolunteerType::class, $volunteer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

        // 🔐 on le hash
        $hashedPassword = $passwordHasher->hashPassword(
            $volunteer,
            $plainPassword
        );

        // 🔐 on stocke le hash
        $volunteer->setPassword($hashedPassword);
            $entityManager->persist($volunteer);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_volunteer_index', [], Response::HTTP_SEE_OTHER);
        }
        $this->addFlash('success', 'Le bénévole a bien été créé.');

        return $this->render('admin_volunteer/new.html.twig', [
            'volunteer' => $volunteer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_volunteer_show', methods: ['GET'])]
    public function show(Volunteer $volunteer): Response
    {
        return $this->render('admin_volunteer/show.html.twig', [
            'volunteer' => $volunteer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_volunteer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Volunteer $volunteer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VolunteerType::class, $volunteer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_volunteer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_volunteer/edit.html.twig', [
            'volunteer' => $volunteer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_volunteer_delete', methods: ['POST'])]
    public function delete(Request $request, Volunteer $volunteer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$volunteer->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($volunteer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_volunteer_index', [], Response::HTTP_SEE_OTHER);
    }
}
