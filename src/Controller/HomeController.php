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
use Symfony\Bundle\SecurityBundle\Security;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils
    ): Response {
        
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('app_admin');
            }
            if (in_array('ROLE_VOLUNTEER', $roles)) {
                return $this->redirectToRoute('app_volunteer');
            }
            if (in_array('ROLE_FAMILY', $roles)) {
                return $this->redirectToRoute('app_family');
            }
        }

        // 🔹 Gestion du login
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // --------------------------------------------------
        // 🔹 INSCRIPTION
        // --------------------------------------------------

        // On récupère le rôle choisi (si le formulaire est soumis)
        $requestData = $request->request->all()['register'] ?? null;
        $roleSelected = $requestData['roles'] ?? null;

        // On crée l'entité AVANT la création du formulaire
        if ($roleSelected === 'ROLE_FAMILY') {
            $user = new Family();
        } elseif ($roleSelected === 'ROLE_VOLUNTEER') {
            $user = new Volunteer();
        } else {
            // Par défaut, avant toute sélection
            $user = new User();
        }

        // Formulaire d'inscription
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {

            // Rôle final (le transformer renvoie une chaîne → on la remet dans un tableau)
            $roles = $form->get('roles')->getData();
            $role = $roles[0] ?? 'ROLE_USER';
            $user->setRoles([$role]);

            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            // Sauvegarde
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'COMPTE_OK|' . $user->getId());

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route('/auto-login/{id}', name: 'app_auto_login')]
    public function autoLogin(
        User $user,
        Request $request,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator
    ) {
        // Login automatique
        return $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );
    }


}
