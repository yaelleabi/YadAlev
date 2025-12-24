<?php

namespace App\Controller\Admin;

use App\Entity\AdminPin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/security')]
class AdminPinController extends AbstractController
{
    private function getOrCreatePin(EntityManagerInterface $em): AdminPin
    {
        $repo = $em->getRepository(AdminPin::class);
        $adminPin = $repo->findOneBy([]);

        if (!$adminPin) {
            $adminPin = new AdminPin();
            // Code par défaut : 1234
            $adminPin->setPinHash(password_hash('1234', PASSWORD_BCRYPT));
            $adminPin->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($adminPin);
            $em->flush();
        }

        return $adminPin;
    }

    #[Route('/pin', name: 'app_admin_pin')]
    public function pin(Request $request, RequestStack $requestStack, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $session = $requestStack->getSession();

        // 1. --- CORRECTIF RETOUR ARRIÈRE ---
        // On vérifie si l'utilisateur a une raison d'être ici.
        // Le Subscriber définit 'admin_pin_target_scope' quand il bloque l'accès.
        // Si cette variable n'existe pas, c'est que l'utilisateur a fait "Retour" ou accès direct.
        $targetScope = $session->get('admin_pin_target_scope');

        if (!$targetScope) {
            // Pas de raison d'être là -> Hop, au tableau de bord !
            return $this->redirectToRoute('app_admin'); 
        }

        // 2. Vérification : est-il DÉJÀ autorisé pour cette zone ? 
        // (Cas où il fait "Précédent" juste après avoir réussi le code)
        $authorizedZones = $session->get('admin_pin_authorized_zones', []);
        $lastAuthTime = $authorizedZones[$targetScope] ?? 0;
        
        // Si autorisé il y a moins de 15 min (900s), on le renvoie direct sur la page cible
        if ($lastAuthTime && (time() - $lastAuthTime) < 900) {
            $redirectTo = $session->get('admin_pin_redirect_to');
            return $this->redirect($redirectTo ?: $this->generateUrl('app_admin'));
        }

        // 3. TRAITEMENT DU FORMULAIRE
        $adminPin = $this->getOrCreatePin($em);
        $error = null;

        if ($request->isMethod('POST')) {
            $pin = (string) $request->request->get('pin', '');

            if (password_verify($pin, $adminPin->getPinHash())) {
                // Succès : on déverrouille la zone
                $authorizedZones[$targetScope] = time();
                $session->set('admin_pin_authorized_zones', $authorizedZones);
                
                // Nettoyage
                $session->remove('admin_pin_target_scope');
                $redirectTo = $session->get('admin_pin_redirect_to');
                $session->remove('admin_pin_redirect_to');

                // Redirection vers la page demandée
                return $this->redirect($redirectTo ?: $this->generateUrl('app_admin'));
            }

            $error = "Code PIN incorrect.";
        }

        // On ajoute un header pour dire au navigateur de ne pas mettre cette page en cache
        // (Ça aide aussi pour le bouton retour)
        $response = $this->render('admin/admin_pin/index.html.twig', [
            'error' => $error
        ]);
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        
        return $response;
    }

    #[Route('/change-pin', name: 'app_admin_pin_change')]
    public function changePin(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $adminPin = $this->getOrCreatePin($em);
        $error = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $oldPin = $request->request->get('old_pin');
            $newPin = $request->request->get('new_pin');
            $confirmPin = $request->request->get('confirm_pin');

            if (!password_verify($oldPin, $adminPin->getPinHash())) {
                $error = "L'ancien code est incorrect.";
            } elseif ($newPin !== $confirmPin) {
                $error = "Les nouveaux codes ne correspondent pas.";
            } elseif (strlen($newPin) < 4 || !ctype_digit($newPin)) {
                $error = "Le code doit comporter au moins 4 chiffres.";
            } else {
                $adminPin->setPinHash(password_hash($newPin, PASSWORD_BCRYPT));
                $adminPin->setUpdatedAt(new \DateTimeImmutable());
                $em->flush();
                $success = "Le code PIN a été modifié avec succès.";
            }
        }

        return $this->render('admin/admin_pin/change.html.twig', [
            'error' => $error,
            'success' => $success
        ]);
    }
}