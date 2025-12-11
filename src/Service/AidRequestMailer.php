<?php

namespace App\Service;

use App\Entity\AidRequest;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class AidRequestMailer
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendAdminNotification(AidRequest $request): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('contact@yadalev.fr', 'Yad Alev'))
            ->to('yaelle.azoulay1311@gmail.com') // remplace par l'adresse admin
            ->subject('Nouvelle demande urgente reçue')
            ->htmlTemplate('email/admin_notification.html.twig')
            ->context([
                'aidRequest' => $request
            ]);

        $this->mailer->send($email);
    }
}
