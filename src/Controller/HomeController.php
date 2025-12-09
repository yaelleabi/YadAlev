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

// 👇 Pour l'email de confirmation
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

// 👇 Pour retrouver l'utilisateur par email
use App\Repository\UserRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils,
        VerifyEmailHelperInterface $verifyEmailHelper, 
        MailerInterface $mailer
    ): Response {
        
        // Redirection si déjà connecté
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) return $this->redirectToRoute('app_admin');
            if (in_array('ROLE_VOLUNTEER', $roles)) return $this->redirectToRoute('app_volunteer');
            if (in_array('ROLE_FAMILY', $roles)) return $this->redirectToRoute('app_family_home');
        }

        // 🔹 Gestion du login
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // --------------------------------------------------
        // 🔹 INSCRIPTION
        // --------------------------------------------------

        $requestData = $request->request->all()['register'] ?? null;
        $roleSelected = $requestData['roles'] ?? null;

        if ($roleSelected === 'ROLE_FAMILY') {
            $user = new Family();
        } elseif ($roleSelected === 'ROLE_VOLUNTEER') {
            $user = new Volunteer();
        } else {
            $user = new User();
        }

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $roles = $form->get('roles')->getData();
            $role = $roles[0] ?? 'ROLE_USER';
            $user->setRoles([$role]);

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            // Important : Par défaut isVerified doit être false dans ton entité User
            $em->persist($user);
            $em->flush();

            // --------------------------------------------------
            // 📧 ENVOI DE L'EMAIL DE CONFIRMATION (INSCRIPTION)
            // --------------------------------------------------
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email', // route de vérification
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $email = (new TemplatedEmail())
                ->from(new Address('yaelle.azoulay1311@gmail.com', 'Ton Site Bot')) // à adapter
                ->to($user->getEmail())
                ->subject('Confirmez votre inscription')
                ->htmlTemplate('register/confirmation_email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                    'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Compte créé ! Vérifiez vos emails pour activer votre compte.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // 🔹🔹 NOUVELLE ROUTE : Renvoyer l’email de vérification 🔹🔹
    #[Route('/resend-verification', name: 'app_resend_verification', methods: ['POST'])]
    public function resendVerification(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {
        $email = $request->request->get('email');

        if (!$email) {
            $this->addFlash('login_error', 'Veuillez renseigner votre adresse e-mail.');
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        // On reste vague pour ne pas divulguer si l'email existe
        if (!$user) {
            $this->addFlash('success', 'Si un compte existe avec cet email, un lien de vérification vous a été renvoyé.');
            return $this->redirectToRoute('app_home');
        }

        if ($user->isVerified()) {
            $this->addFlash('success', 'Votre compte est déjà vérifié. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_home');
        }

        // Générer une nouvelle signature
        $signatureComponents = $verifyEmailHelper->generateSignature(
            'app_verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('yaelle.azoulay1311@gmail.com', 'Ton Site Bot')) // même from que l'inscription
            ->to($user->getEmail())
            ->subject('Nouveau lien de confirmation')
            ->htmlTemplate('register/confirmation_email.html.twig')
            ->context([
                'signedUrl' => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
            ]);

        $mailer->send($emailMessage);

        $this->addFlash('success', 'Un email de vérification vous a été renvoyé.');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/auto-login/{id}', name: 'app_auto_login')]
    public function autoLogin(
        User $user,
        Request $request,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator
    ) {
        return $userAuthenticator->authenticateUser(
            $user,
            $authenticator,
            $request
        );
    }
}
