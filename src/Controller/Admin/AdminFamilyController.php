<?php

namespace App\Controller\Admin;

use App\Repository\AidRequestRepository;
use App\Repository\FamilyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Entity\Family;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\FamilyType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\AdminFamilyCreateType;

#[IsGranted(attribute: 'ROLE_ADMIN')]
final class AdminFamilyController extends AbstractController
{
    #[Route('/admin/families', name: 'app_admin_family_list')]
    public function listFamilies(
        FamilyRepository $familyRepository,
        AidRequestRepository $aidRequestRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $families = $familyRepository->findAll();
        $validatedRequests = [];

        foreach ($families as $family) {
            $validatedRequests[$family->getId()] =
                $aidRequestRepository->findValidatedByFamily($family);
        }

        return $this->render('admin/admin_family/index.html.twig', [
            'families' => $families,
            'validatedRequests' => $validatedRequests
        ]);
    }
     #[Route('/admin/family/new', name: 'admin_family_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $family = new Family();

        $form = $this->createForm(AdminFamilyCreateType::class, $family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $family->setPassword($hasher->hashPassword($family, $plainPassword));

            $family->setRoles(['ROLE_FAMILY']);
            $family->setIsVerified(true);


           

            $em->persist($family);
            $em->flush();

            $this->addFlash('success', 'La famille a bien été créée.');
            return $this->redirectToRoute('app_admin_family_list');
        }

        return $this->render('admin/admin_family/new.html.twig', [
            'form' => $form->createView(),
            'family' => $family,
        ]);
    }
    #[Route('/admin/families/export/excel', name: 'app_family_export_excel')]
    public function exportExcel(
        FamilyRepository $familyRepository,
        AidRequestRepository $aidRequestRepository
    ): Response {
        $families = $familyRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->fromArray(
            ['Nom', 'Email', 'Téléphone', 'Aides attribuées', 'Date'],
            null,
            'A1'
        );

        $row = 2;

        foreach ($families as $family) {
            $validated = $aidRequestRepository->findBy([
                'family' => $family,
                'status' => \App\Enum\AidRequestStatus::VALIDATED
            ]);

            if (empty($validated)) {
                $sheet->setCellValue("A$row", $family->getName());
                $sheet->setCellValue("B$row", $family->getEmail());
                $sheet->setCellValue("C$row", $family->getPhoneNumber());
                $sheet->setCellValue("D$row", "Aucune");
                $sheet->setCellValue("E$row", "-");
                $row++;
            } else {
                foreach ($validated as $aid) {
                    $sheet->setCellValue("A$row", $family->getName());
                    $sheet->setCellValue("B$row", $family->getEmail());
                    $sheet->setCellValue("C$row", $family->getPhoneNumber());
                    $sheet->setCellValue("D$row", $aid->getRequestType());
                    $sheet->setCellValue("E$row", $aid->getCreatedAt()->format('d/m/Y'));
                    $row++;
                }
            }
        }

        $response = new StreamedResponse(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="liste_familles.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;


        
    }
    #[Route('/admin/family/{id}', name: 'admin_family_show', methods: ['GET'])]
    public function show(
        Family $family,
        AidRequestRepository $aidRequestRepository
    ): Response {
        $validatedRequests = $aidRequestRepository->findValidatedByFamily($family);

        return $this->render('admin/admin_family/show.html.twig', [
            'family' => $family,
            'validatedRequests' => $validatedRequests
        ]);
    }

    #[Route('/admin/family/{id}/edit', name: 'admin_family_edit', methods: ['GET', 'POST'])]
    public function edit(
        Family $family,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $form = $this->createForm(FamilyType::class, $family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Les informations de la famille ont été mises à jour.');

            return $this->redirectToRoute('admin_family_show', [
                'id' => $family->getId()
            ]);
        }

        return $this->render('admin/admin_family/edit.html.twig', [
            'family' => $family,
            'form' => $form,
        ]);
    }

}
