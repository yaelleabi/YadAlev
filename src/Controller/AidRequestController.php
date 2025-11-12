<?php
namespace App\Controller;

use App\Entity\AidRequest;
use App\Form\AidRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AidRequestController extends AbstractController
{
    #[Route('/family/aidrequest/new', name: 'app_aidrequest_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $aidRequest = new AidRequest();
        $aidRequest->setFamily($this->getUser());

        // ✅ Pré-remplir avec les infos du user connecté
        $user = $this->getUser();
        if ($user) {
            if (method_exists($user, 'getName')) {
                $aidRequest->setFirstName($user->getName());
            }
            if (method_exists($user, 'getName')) {
                $aidRequest->setLastName($user->getName());
            }
            if (method_exists($user, 'getEmail')) {
                $aidRequest->setEmail($user->getEmail());
            }
            if (method_exists($user, 'getPhoneNumber')) {
                $aidRequest->setPhoneNumber($user->getPhoneNumber());
            }
        }

        $form = $this->createForm(AidRequestType::class, $aidRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($aidRequest);
            $em->flush();

            $this->addFlash('success', 'Votre demande a bien été envoyée.');
            return $this->redirectToRoute('app_family');
        }

        return $this->render('aid_request/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
