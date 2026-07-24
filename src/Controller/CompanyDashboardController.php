<?php

namespace App\Controller;

use App\Repository\ListingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompanyDashboardController extends AbstractController
{
    #[Route('/dashboard/company', name: 'company_dashboard')]
    public function companyDashboard(ListingRepository $listingRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY', null, 'You cannot access this page');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $listings = $listingRepository->findBy(
            ['company' => $user->getCompany()],
            ['createdAt' => 'DESC']
        );

        return $this->render('company_dashboard/index.html.twig', [
            'listings' => $listings,
        ]);
    }
}
