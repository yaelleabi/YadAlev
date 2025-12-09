<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegisterController extends AbstractController
{
    // 👇 Cette méthode ne sert qu'à afficher une page d'erreur si besoin.
    // On a retiré toute la logique de création de compte car elle est dans HomeController.
    #[Route('/register', name: 'app_register')]
    public function register(): Response 
    {
         return $this->render('register/index.html.twig');
    }

    // 👇 C'est LA méthode importante de ce fichier : Elle valide le clic dans l'email.
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request, 
        VerifyEmailHelperInterface $verifyEmailHelper, 
        UserRepository $userRepository, 
        EntityManagerInterface $entityManager
    ): Response {
        
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_home');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());
            return $this->redirectToRoute('app_home'); // Ou app_register si tu veux afficher l'erreur
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Votre email a été vérifié ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_home'); 
    }
}