<?php

namespace App\Service;

use App\Entity\AidRequest;
use App\Entity\Family;

class FamilySyncService
{
    /**
     * 🔄 1. Met à jour Family depuis une AidRequest
     * Appelé quand une AideRequest est créée ou modifiée par la famille.
     */
    public function updateFamilyFromAidRequest(Family $family, AidRequest $aidRequest): void
    {
        $family->setFirstName($aidRequest->getFirstName());
        $family->setName($aidRequest->getLastName());
        $family->setDateOfBirth($aidRequest->getDateOfBirth());
        $family->setEmail($aidRequest->getEmail());
        $family->setPhoneNumber($aidRequest->getPhoneNumber());

        if ($aidRequest->getAdress()) {
            // clone impératif pour éviter les références partagées
            $family->setAdress(clone $aidRequest->getAdress());
        }

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
    }

    /**
     * 🧩 2. Pré-remplit une AidRequest à partir de Family
     * Appelé uniquement pour NEW et RENEW.
     * Les anciennes demandes NE sont pas modifiées.
     */
    public function fillAidRequestFromFamily(Family $family, AidRequest $aidRequest): void
    {
        $aidRequest->setFirstName($family->getFirstName());
        $aidRequest->setLastName($family->getName());
        $aidRequest->setDateOfBirth($family->getDateOfBirth());
        $aidRequest->setEmail($family->getEmail());
        $aidRequest->setPhoneNumber($family->getPhoneNumber());

        if ($family->getAdress()) {
            // clone obligatoire pour éviter que Family & AidRequest pointent sur le même objet
            $aidRequest->setAdress(clone $family->getAdress());
        }

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
    }
}
