<?php
namespace App\Controller;

use App\Entity\AidRequest;
use App\Form\AidRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class AidRequestController extends AbstractController
{
    #[Route('/family/aidrequest/new', name: 'app_aidrequest_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $aidRequest = new AidRequest();
        $aidRequest->setFamily($this->getUser());

        // ✅ Pré-remplir avec les infos du user connecté
        $user = $this->getUser();
        if ($user) {
            if (method_exists($user, 'getFirstName')) {
                $aidRequest->setFirstName($user->getFirstName());
            } elseif (method_exists($user, 'getName')) {
                // fallback si ton entity Family utilise "name"
                $aidRequest->setFirstName($user->getName());
            }

            if (method_exists($user, 'getLastName')) {
                $aidRequest->setLastName($user->getLastName());
            } elseif (method_exists($user, 'getName')) {
                $aidRequest->setLastName($user->getName());
            }

            if (method_exists($user, 'getEmail')) {
                $aidRequest->setEmail($user->getEmail());
            }
            if (method_exists($user, 'getPhoneNumber')) {
                $aidRequest->setPhoneNumber($user->getPhoneNumber());
            }
        }

        // ✅ Formulaire avec l’option is_family comme avant
        $form = $this->createForm(AidRequestType::class, $aidRequest, [
            'is_family' => $this->isGranted('ROLE_FAMILY'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 📁 Dossier où tu stockes physiquement les fichiers
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';

            // 🔹 Tous les champs FileType du formulaire
            $fileFields = [
                'identityProofFilename',
                'incomeProofFilename',
                'taxNoticeFilename',
                'quittanceLoyer',
                'avisCharge',
                'taxeFonciere',
                'fraisScolarite',
                'attestationCaf',
                'otherDocumentFilename',
            ];

            foreach ($fileFields as $fieldName) {
                $file = $form->get($fieldName)->getData();

                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                    try {
                        $file->move($uploadDir, $newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors du téléchargement d’un fichier.');
                        // On continue quand même pour les autres fichiers
                        continue;
                    }

                    // 🔗 Setter dynamique sur l’entité (setIdentityProofFilename, etc.)
                    $setter = 'set' . ucfirst($fieldName);
                    if (method_exists($aidRequest, $setter)) {
                        $aidRequest->$setter($newFilename);
                    }
                }
            }

            $em->persist($aidRequest);
            $em->flush();

            // Tu avais une page de succès dédiée, on la garde
            return $this->redirectToRoute('app_aidrequest_success');
        }

        return $this->render('aid_request/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/family/aidrequest/success', name: 'app_aidrequest_success')]
    public function success(): Response
    {
        return $this->render('aid_request/success.html.twig');
    }
    #[Route('/family/aidrequest/{id}', name: 'app_aidrequest_show')]
    public function show(AidRequest $aidRequest): Response
    {
        // Sécurité : une famille ne peut voir QUE ses propres demandes
        if ($this->isGranted('ROLE_FAMILY') && $aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('aid_request/show.html.twig', [
            'aidRequest' => $aidRequest,
        ]);
    }
    #[Route('/family/aidrequest/{id}/edit', name: 'app_aidrequest_edit')]
    public function edit(Request $request, AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        // Sécurité : une famille ne peut modifier QUE sa propre demande
        if ($this->isGranted('ROLE_FAMILY') && $aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AidRequestType::class, $aidRequest, [
            'is_family' => $this->isGranted('ROLE_FAMILY'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Votre demande a bien été mise à jour.');
            return $this->redirectToRoute('app_aidrequest_show', ['id' => $aidRequest->getId()]);
        }

        return $this->render('aid_request/edit.html.twig', [
            'form' => $form->createView(),
            'aidRequest' => $aidRequest,
        ]);
    }
    #[Route('/family/aidrequest/{id}/delete', name: 'app_aidrequest_delete', methods: ['POST'])]
    public function delete(Request $request, AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        // Sécurité : une famille ne peut supprimer QUE sa propre demande
        if ($this->isGranted('ROLE_FAMILY') && $aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$aidRequest->getId(), $request->request->get('_token'))) {
            $em->remove($aidRequest);
            $em->flush();

            $this->addFlash('success', 'La demande a été supprimée.');
        }

        return $this->redirectToRoute('app_family');
    }
   



}
