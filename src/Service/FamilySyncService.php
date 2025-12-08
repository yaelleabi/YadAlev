<?php

namespace App\Service;

use App\Entity\AidRequest;
use App\Entity\Family;

class FamilySyncService
{
    public function updateFromAidRequest(Family $family, AidRequest $aidRequest): void
    {
        $family->setFirstName($aidRequest->getFirstName());
        $family->setDateOfBirth($aidRequest->getDateOfBirth());
        $family->setEmail($aidRequest->getEmail());
        $family->setPhoneNumber($aidRequest->getPhoneNumber());
        $family->setAdress($aidRequest->getAdress());
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
}
