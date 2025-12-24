<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminPinSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator,
        private AuthorizationCheckerInterface $authChecker,
        private int $ttlSeconds,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
        ];
    }

   // src/EventSubscriber/AdminPinSubscriber.php

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // 1. Définition des zones protégées
        $protectedPrefixes = [
            '/admin/families',    // Zone 1
            '/admin/aidrequests', // Zone 2
        ];

        $currentPrefix = null;
        
        // 2. On regarde dans quelle zone on se trouve
        foreach ($protectedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $currentPrefix = $prefix;
                break;
            }
        }

        // Si on n'est pas dans une zone protégée, on laisse passer
        if (!$currentPrefix) {
            return;
        }

        // Admin uniquement
        if (!$this->authChecker->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Ne pas bloquer la page du PIN elle-même !
        $route = (string) $request->attributes->get('_route');
        if ($route === 'app_admin_pin') {
            return;
        }

        // 3. Gestion de la Session
        if (!$request->hasSession()) {
            return;
        }
        $session = $request->getSession();
        
        // On récupère le tableau des zones déverrouillées
        // Structure : ['/admin/families' => timestamp, '/admin/aidrequests' => timestamp]
        $authorizedZones = $session->get('admin_pin_authorized_zones', []);

        // Vérification pour LA zone en cours
        $lastAuthTime = $authorizedZones[$currentPrefix] ?? 0;
        $expired = (time() - $lastAuthTime) > $this->ttlSeconds;

        // Si pas autorisé ou expiré
        if (!$lastAuthTime || $expired) {
            
            // On mémorise quelle zone l'utilisateur voulait voir
            $session->set('admin_pin_target_scope', $currentPrefix);
            
            // On mémorise l'URL complète pour le rediriger après
            $session->set('admin_pin_redirect_to', $request->getUri());

            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_admin_pin')
            ));
        }
    }

}
