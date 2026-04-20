<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ApplicationRepository;

final class FreelancerDashboardController extends AbstractController
{
    #[Route('/freelancer/dashboard', name: 'freelancer_dashboard')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');
        
        return $this->render('freelancer_dashboard/index.html.twig', [
            'controller_name' => 'FreelancerDashboardController',
        ]);
    }

    #[Route('/dashboard/applications', name: 'app_freelancer_dashboard')]
    public function freelancerDashboard(ApplicationRepository $applicationRepository): Response {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');

        $applications = $applicationRepository->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('freelancer_dashboard/applications.html.twig', [
            'applications' => $applications,
        ]);
    }
}
