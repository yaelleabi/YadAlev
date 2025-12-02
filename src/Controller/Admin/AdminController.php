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


class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/aidproject/new', name: 'app_admin_aidproject_new')]
    public function createAidProject(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/aidproject_new.html.twig');
    }

   
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

        return $this->render('family/index.html.twig', [
            'families' => $families,
            'validatedRequests' => $validatedRequests
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
}