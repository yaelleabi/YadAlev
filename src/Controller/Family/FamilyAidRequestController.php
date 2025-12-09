<?php

namespace App\Controller\Family;

use App\Entity\AidRequest;
use App\Form\AidRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Enum\AidRequestStatus;
use App\Repository\AidRequestRepository;
use App\Entity\Family;

final class FamilyAidRequestController extends AbstractController
{
    #[Route('/family/aid/request', name: 'app_family_aid_request')]
    public function index(): Response
    {
        return $this->render('family/family_aid_request/index.html.twig');
    }


    /* ========================== NEW AID REQUEST ========================== */

    #[Route('/family/aidrequest/new', name: 'app_aidrequest_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if ($em->getRepository(AidRequest::class)->findOneBy(['family' => $user])) {
            return $this->redirectToRoute('app_aidrequest_existing');
        }

        $aidRequest = new AidRequest();
        $aidRequest->setFamily($user);
        $aidRequest->setStatus(AidRequestStatus::PENDING);

        if ($user instanceof Family) {
            $aidRequest->setLastName($user->getName());
            $aidRequest->setEmail($user->getEmail());
            $aidRequest->setPhoneNumber($user->getPhoneNumber());
        }

        $form = $this->createForm(AidRequestType::class, $aidRequest, ['is_family' => true]);
        $form->handleRequest($request);
        



        if ($form->isSubmitted() && $form->isValid()) {
            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads';
            $files = ['identityProofFilename','incomeProofFilename','taxNoticeFilename','quittanceLoyer','avisCharge',
                      'taxeFonciere','fraisScolarite','attestationCaf','otherDocumentFilename'];

            foreach ($files as $field) {
                $file = $form->get($field)->getData();
                if ($file) {
                    $name = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            .'-'.uniqid().'.'.$file->guessExtension();
                    $file->move($uploadDir,$name);
                    $setter = 'set'.ucfirst($field);
                    $aidRequest->$setter($name);
                }
            }

            $em->persist($aidRequest);
            $em->flush();

            return $this->redirectToRoute('app_aidrequest_success');
        }

        return $this->render('aid_request/new.html.twig', parameters: ['form'=>$form->createView()]);
    }


    #[Route('/family/aidrequest/success', name: 'app_aidrequest_success')]
    public function success(): Response
    {
        return $this->render('aid_request/success.html.twig');
    }

    #[Route('/family/aidrequest/existing', name: 'app_aidrequest_existing')]
    public function existing(AidRequestRepository $repo): Response
    {
        $aidRequest = $repo->findOneBy(['family'=>$this->getUser()],['createdAt'=>'DESC']);
        $family = $this->getUser();
        return $aidRequest
            ? $this->render('family/family_aid_request/existing.html.twig',['aid_request'=>$aidRequest ,'family'=>$family])
            : $this->redirectToRoute('app_aidrequest_new');
    }


    /* ============================== EDIT ============================== */

    #[Route('/family/aidrequest/{id}', name:'app_aidrequest_show')]
    public function showFamily(AidRequest $aidRequest): Response
    {
        if ($aidRequest->getFamily() !== $this->getUser()) throw $this->createAccessDeniedException();
        return $this->render('family/family_aid_request/show.html.twig',['aid_request'=>$aidRequest]);
    }

   #[Route('/family/aidrequest/{id}/edit', name:'app_aidrequest_edit')]
    public function editFamily(Request $request, AidRequest $aidRequest, EntityManagerInterface $em): Response
    {
        if ($aidRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** @var \App\Entity\Family $family */
        $family = $this->getUser();

        $form = $this->createForm(AidRequestType::class, $aidRequest, ['is_family' => true]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

        // 🔁 Mettre à jour Family à partir de AidRequest
        $family->setName($aidRequest->getLastName());
        $family->setFirstName($aidRequest->getFirstName());
        $family->setDateOfBirth($aidRequest->getDateOfBirth());
        $family->setEmail($aidRequest->getEmail());
        $family->setPhoneNumber($aidRequest->getPhoneNumber());
        if ($aidRequest->getAdress()) { $family->setAdress(clone $aidRequest->getAdress()); }
        $family->setHousingStatus($aidRequest->getHousingStatus());
        $family->setMaritalStatus($aidRequest->getMaritalStatus());
        $family->setDependantsCount($aidRequest->getDependantsCount());
        $family->setEmploymentStatus($aidRequest->getEmploymentStatus());
        $family->setMonthlyIncome($aidRequest->getMonthlyIncome());
        $family->setSpouseEmploymentStatus($aidRequest->getSpouseEmploymentStatus());
        $family->setSpouseMonthlyIncome($aidRequest->getSpouseMonthlyIncome());
        $family->setFamilyAllowanceAmount($aidRequest->getFamilyAllowanceAmount());
        $family->setAlimonyAmount($aidRequest->getAlimonyAmount());
        $family->setRentAmountNetAide($aidRequest->getRentAmountNetAide());
        $family->setOtherNeed($aidRequest->getOtherNeed());
        $family->setOtherComment($aidRequest->getOtherComment());

        // 🔁 Mettre à jour AidRequest avec les mêmes données si tu veux qu'elles s'affichent dans SHOW
        // (sinon, il gardera les anciennes valeurs affichées)
        $aidRequest->setLastName($family->getName());
        $aidRequest->setFirstName($family->getFirstName());
        $aidRequest->setDateOfBirth($family->getDateOfBirth());
        $aidRequest->setEmail($family->getEmail());
        $aidRequest->setPhoneNumber($family->getPhoneNumber());
        if ($family->getAdress()) { $aidRequest->setAdress(clone $family->getAdress()); }
        $aidRequest->setHousingStatus($family->getHousingStatus());
        $aidRequest->setMaritalStatus($family->getMaritalStatus());
        $aidRequest->setDependantsCount($family->getDependantsCount());
        $aidRequest->setEmploymentStatus($family->getEmploymentStatus());
        $aidRequest->setMonthlyIncome($family->getMonthlyIncome());
        $aidRequest->setSpouseEmploymentStatus($family->getSpouseEmploymentStatus());
        $aidRequest->setSpouseMonthlyIncome($family->getSpouseMonthlyIncome());
        $aidRequest->setFamilyAllowanceAmount($family->getFamilyAllowanceAmount());
        $aidRequest->setAlimonyAmount($family->getAlimonyAmount());
        $aidRequest->setRentAmountNetAide($family->getRentAmountNetAide());
        $aidRequest->setOtherNeed($family->getOtherNeed());
        $aidRequest->setOtherComment($family->getOtherComment());

        $em->flush();
        return $this->redirectToRoute('app_aidrequest_show',['id'=>$aidRequest->getId()]);
    }


        return $this->render('family/family_aid_request/edit.html.twig',[
            'form'=>$form->createView()
        ]);
    }


    /* ============================== RENEW ============================== */

    #[Route('/family/aidrequest/{id}/renew', name: 'app_aidrequest_renew')]
    public function renew(AidRequest $oldRequest): Response
    {
        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** @var \App\Entity\Family $family */
        $family = $this->getUser();

        // Nouvelle demande NE PREND QUE FAMILY !!!
        $new = new AidRequest();
        $new->setFamily($family);
        $new->setStatus(AidRequestStatus::PENDING);
        $new->setCreatedAt(new \DateTimeImmutable());
        $new->setIsUpdated(false);

        // ⚡ On remplit 100% du formulaire avec FAMILY
        $new->setLastName($family->getName());
        $new->setFirstName($family->getFirstName());
        $new->setDateOfBirth($family->getDateOfBirth());
        $new->setEmail($family->getEmail());
        $new->setPhoneNumber($family->getPhoneNumber());
        $new->setAdress(clone $family->getAdress());

        // Champs financiers & situ — uniquement depuis Family
        $new->setHousingStatus($family->getHousingStatus());
        $new->setMaritalStatus($family->getMaritalStatus());
        $new->setDependantsCount($family->getDependantsCount());
        $new->setEmploymentStatus($family->getEmploymentStatus());
        $new->setMonthlyIncome($family->getMonthlyIncome());
        $new->setSpouseEmploymentStatus($family->getSpouseEmploymentStatus());
        $new->setSpouseMonthlyIncome($family->getSpouseMonthlyIncome());
        $new->setFamilyAllowanceAmount($family->getFamilyAllowanceAmount());
        $new->setAlimonyAmount($family->getAlimonyAmount());
        $new->setRentAmountNetAide($family->getRentAmountNetAide());
        $new->setOtherNeed($family->getOtherNeed());
        $new->setOtherComment($family->getOtherComment());

        // Formulaire affiché → basé UNIQUEMENT sur Family
        $form = $this->createForm(AidRequestType::class, $new, [
            'is_family' => true
        ]);

        return $this->render('aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest
        ]);
    }


    #[Route('/family/aidrequest/{id}/renew-submit', name: 'app_aidrequest_renew_submit')]
    public function renewSubmit(Request $request, AidRequest $oldRequest, EntityManagerInterface $em, SluggerInterface $slugger): Response
    { 
       
     
        // 1. Sécurité
        if ($oldRequest->getFamily() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        /** @var \App\Entity\Family $family */
        $family = $this->getUser();

        // 2. Création de l'objet vide
        $new = new AidRequest();
        $new->setFamily($family);
        $new->setCreatedAt(new \DateTimeImmutable());
        $new->setStatus(AidRequestStatus::PENDING);
        $new->setIsUpdated(false);

        // 3. PRÉ-REMPLISSAGE (Identique à ta logique)
        $new->setLastName($family->getName() ?? $oldRequest->getLastName());
        $new->setFirstName($family->getFirstName() ?? $oldRequest->getFirstName());
        $new->setDateOfBirth($family->getDateOfBirth() ?? $oldRequest->getDateOfBirth());
        $new->setEmail($family->getEmail() ?? $oldRequest->getEmail());
        $new->setPhoneNumber($family->getPhoneNumber() ?? $oldRequest->getPhoneNumber());
        
        if ($family->getAdress()) {
             $new->setAdress(clone $family->getAdress());
        } elseif ($oldRequest->getAdress()) {
             $new->setAdress(clone $oldRequest->getAdress());
        }

        $new->setHousingStatus($family->getHousingStatus() ?? $oldRequest->getHousingStatus());
        $new->setMaritalStatus($family->getMaritalStatus() ?? $oldRequest->getMaritalStatus());
        $new->setDependantsCount($family->getDependantsCount() ?? $oldRequest->getDependantsCount());
        $new->setEmploymentStatus($family->getEmploymentStatus() ?? $oldRequest->getEmploymentStatus());
        $new->setMonthlyIncome($family->getMonthlyIncome() ?? $oldRequest->getMonthlyIncome());
        $new->setSpouseEmploymentStatus($family->getSpouseEmploymentStatus() ?? $oldRequest->getSpouseEmploymentStatus());
        $new->setSpouseMonthlyIncome($family->getSpouseMonthlyIncome() ?? $oldRequest->getSpouseMonthlyIncome());
        $new->setFamilyAllowanceAmount($family->getFamilyAllowanceAmount() ?? $oldRequest->getFamilyAllowanceAmount());
        $new->setAlimonyAmount($family->getAlimonyAmount() ?? $oldRequest->getAlimonyAmount());
        $new->setRentAmountNetAide($family->getRentAmountNetAide() ?? $oldRequest->getRentAmountNetAide());
        $new->setOtherNeed($family->getOtherNeed() ?? $oldRequest->getOtherNeed());
        $new->setOtherComment($family->getOtherComment() ?? $oldRequest->getOtherComment());

        // Pré-remplissage des noms de fichiers (crucial pour éviter les erreurs de validation si le champ est "required" dans l'entité mais pas dans le form)
        $new->setIdentityProofFilename($oldRequest->getIdentityProofFilename());
        $new->setIncomeProofFilename($oldRequest->getIncomeProofFilename());
        $new->setTaxNoticeFilename($oldRequest->getTaxNoticeFilename());
        $new->setOtherDocumentFilename($oldRequest->getOtherDocumentFilename());
        $new->setQuittanceLoyer($oldRequest->getQuittanceLoyer());
        $new->setAvisCharge($oldRequest->getAvisCharge());
        $new->setTaxeFonciere($oldRequest->getTaxeFonciere());
        $new->setFraisScolarite($oldRequest->getFraisScolarite());
        $new->setAttestationCaf($oldRequest->getAttestationCaf());

        // 4. Création du formulaire et fusion avec les données POST
        $form = $this->createForm(AidRequestType::class, $new, ['is_family' => true]);
        $form->handleRequest($request);

        // 5. Validation et Persistance
        if ($form->isSubmitted() && $form->isValid()) {

            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads';
            $fileFields = [
                'identityProofFilename','incomeProofFilename','taxNoticeFilename',
                'quittanceLoyer','avisCharge','taxeFonciere',
                'fraisScolarite','attestationCaf','otherDocumentFilename'
            ];

            foreach ($fileFields as $field) {
                $file = $form->get($field)->getData();
                
                if ($file) {
                    // Nouveau fichier uploadé -> on traite
                    $newFilename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                                    . '-' . uniqid() . '.' . $file->guessExtension();
                    try {
                        $file->move($uploadDir, $newFilename);
                        $setter = 'set'.ucfirst($field);
                        if (method_exists($new, $setter)) {
                            $new->$setter($newFilename);
                        }
                    } catch (FileException $e) {
                        // Log l'erreur si besoin
                    }
                } 
                // Si pas de fichier ($file est null), $new a déjà la valeur de l'ancien fichier 
                // grâce à ton étape 3 (Pré-remplissage). C'est parfait.
            }

            $em->persist($new);
            $em->flush();

            $this->addFlash('success', 'Votre demande de renouvellement a bien été enregistrée.');
            return $this->redirectToRoute('app_aidrequest_success');
        }

        // Si on arrive ici, c'est qu'il y a des erreurs. 
        // Le dump ci-dessous apparaîtra UNIQUEMENT s'il y a un problème.
        if ($form->isSubmitted() && !$form->isValid()) {
             // Tu peux décommenter ceci temporairement pour voir les erreurs précises
             // dd($form->getErrors(true)); 
        }

        return $this->render('aid_request/renew.html.twig', [
            'form' => $form->createView(),
            'oldRequest' => $oldRequest,
        ]);
    }
}
