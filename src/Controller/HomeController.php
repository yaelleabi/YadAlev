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
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use App\Repository\UserRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;


class HomeController extends AbstractController
{ 
    private LocaleAwareInterface $translator;

    public function __construct(LocaleAwareInterface $translator)
    {
        $this->translator = $translator;
    }


    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AuthenticationUtils $authenticationUtils,
        VerifyEmailHelperInterface $verifyEmailHelper, 
        MailerInterface $mailer
    ): Response {
        
        // 🔹 Redirection si déjà connecté
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
        // 🔹 Détection du rôle choisi dans le formulaire
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

        // --------------------------------------------------
        // 🔹 Validation du formulaire d'inscription
        // --------------------------------------------------
        if ($form->isSubmitted() && $form->isValid()) {

            // Définir le rôle choisi
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

            // --------------------------------------------------
            // 📧 Envoi de l'email de confirmation
            // --------------------------------------------------

            // Important : forcer les traductions en FR
            $this->translator->setLocale('fr');

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $email = (new TemplatedEmail())
                ->from(new Address('contact@yadalev.fr', 'Yad Alev'))
                ->to($user->getEmail())
                ->subject('Confirmez votre inscription')
                ->htmlTemplate('email/confirmation_email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                    'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                ]);

            $mailer->send($email);

            $this->addFlash('success', 
                'Votre compte a bien été créé. Consultez vos e-mails et cliquez sur le lien de confirmation pour l’activer.'
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // --------------------------------------------------
    // 🔹 Renvoyer un email de vérification
    // --------------------------------------------------
    #[Route('/resend-verification', name: 'app_resend_verification', methods: ['POST'])]
    public function resendVerification(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {

        // Toujours forcer FR ici aussi
        $this->translator->setLocale('fr');

        $email = $request->request->get('email');

        if (!$email) {
            $this->addFlash('login_error', 'Veuillez renseigner votre adresse e-mail.');
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        // Ne pas divulguer si un mail existe
        if (!$user) {
            $this->addFlash('success', 'Si un compte existe avec cet email, un lien de vérification vous a été renvoyé.');
            return $this->redirectToRoute('app_home');
        }

        if ($user->isVerified()) {
            $this->addFlash('success', 'Votre compte est déjà vérifié. Vous pouvez vous connecter.');
            return $this->redirectToRoute('app_home');
        }

        // Génération d'une nouvelle signature
        $signatureComponents = $verifyEmailHelper->generateSignature(
            'app_verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $emailMessage = (new TemplatedEmail())
            ->from(new Address('contact@yadalev.fr', 'Yad Alev'))
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
    #[Route('/_dynamic_css', name: 'app_dynamic_css')]
    public function dynamicCss(\App\Repository\SettingRepository $settingRepository): Response
    {
        $color = $settingRepository->getValue('site_title_color', '#0d6efd');
        
        $css = "
            :root { --primary-color: {$color}; }
            .text-primary { color: {$color} !important; }
            .btn-primary { background-color: {$color} !important; border-color: {$color} !important; }
            .btn-outline-primary { color: {$color} !important; border-color: {$color} !important; }
            .btn-outline-primary:hover { background-color: {$color} !important; color: #fff !important; }
            .page-link { color: {$color} !important; }
            .active > .page-link, .page-link.active { background-color: {$color} !important; border-color: {$color} !important; }
        ";

        return new Response($css, 200, ['Content-Type' => 'text/css']);
    }
}
