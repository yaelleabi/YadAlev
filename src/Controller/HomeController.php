<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Family;
use App\Entity\Volunteer;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils
    ): Response {
        // 🔹 Gestion du login
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // 🔹 Gestion de l’inscription
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ✅ Déterminer le rôle choisi (ex: depuis le formulaire)
            $roles = $form->get('roles')->getData();
            $role = $roles[0] ?? 'ROLE_USER';

            // ✅ Créer l'entité correspondante selon le rôle
            if ($role === 'ROLE_FAMILY') {
                $user = new Family();
            } elseif ($role === 'ROLE_VOLUNTEER') {
                $user = new Volunteer();
            } else {
                $user = new User();
            }

            // ✅ Renseigner les champs communs
            $user->setEmail($form->get('email')->getData());
            $user->setName($form->get('name')->getData());
            $user->setPhoneNumber($form->get('phoneNumber')->getData());
            $user->setRoles([$role]);

            // ✅ Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Compte créé avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
