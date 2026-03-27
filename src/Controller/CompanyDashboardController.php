<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CompanyDashboardController extends AbstractController
{
    #[Route('/company/dashboard', name: 'company_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY', null, 'You cannot access this page');

        return $this->render('company_dashboard/index.html.twig', [
            'controller_name' => 'CompanyDashboardController',
        ]);
    }
}
