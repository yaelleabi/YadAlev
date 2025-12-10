<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login'; // route de connexion

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password');
        $csrfToken = $request->request->get('_csrf_token');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function (string $userIdentifier) {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

                if (!$user) {
                    throw new CustomUserMessageAuthenticationException(
                        'Identifiants incorrects. Vérifiez votre email et votre mot de passe.'
                    );
                }

                // 🔽🔽🔽 TEMPORAIREMENT DÉSACTIVÉ : blocage si l'email n'est pas vérifié 🔽🔽🔽
                /*
                if (!$user->isVerified()) {
                    throw new CustomUserMessageAuthenticationException(
                        'Veuillez vérifier votre adresse e-mail avant de vous connecter.'
                    );
                }
                */
                // 🔼🔼🔼 FIN DU BLOC COMMENTÉ 🔼🔼🔼

                return $user;
            }),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var UserInterface $user */
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Déterminez la route de redirection en fonction des rôles de l'utilisateur
        $route = 'app_home'; // Valeur par défaut

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $route = 'app_admin'; // Route pour les administrateurs
        } elseif (in_array('ROLE_VOLUNTEER', $roles, true)) {
            $route = 'app_volunteer'; // Route pour les bénévoles
        } elseif (in_array('ROLE_FAMILY', $roles, true)) {
            $route = 'app_family_home'; // Route pour les familles
        }

        // Redirection vers la route appropriée
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate($route));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = $exception->getMessageKey();

        // Tu peux laisser ce bloc, il ne sera plus utilisé tant que le check isVerified() est commenté
        if ($message === 'Veuillez vérifier votre adresse e-mail avant de vous connecter.') {
            $request->getSession()->getFlashBag()->add('verify_email', $message);
        } else {
            $request->getSession()->getFlashBag()->add('login_error', $message);
        }

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
