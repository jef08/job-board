<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ApplicationRepository;
use App\Repository\ListingRepository;
use App\Form\FreelancerProfileFormType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


final class FreelancerDashboardController extends AbstractController
{
#[Route('/dashboard/freelancer', name: 'freelancer_dashboard')]
    public function index(ListingRepository $listingRepository, ApplicationRepository $applicationRepository, CompanyRepository $companyRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $featuredListings = $listingRepository->findRecentOpen(5);

        $recentApplications = $applicationRepository->findBy(
            ['freelancer' => $user->getFreelancer()],
            ['createdAt' => 'DESC'],
            5
        );

        $featuredCompanies = $companyRepository->findFeatured(5);

        return $this->render('freelancer_dashboard/index.html.twig', [
            'featuredListings' => $featuredListings,
            'recentApplications' => $recentApplications,
            'featuredCompanies' => $featuredCompanies,
        ]);
    }

    #[Route('/dashboard/applications', name: 'app_freelancer_applications')]
    public function freelancerApplications(ApplicationRepository $applicationRepository): Response {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $applications = $applicationRepository->findBy(
            ['freelancer' => $user->getFreelancer()],
            ['createdAt' => 'DESC']
        );

        return $this->render('freelancer_dashboard/applications.html.twig', [
            'applications' => $applications,
        ]);
    }

    #[Route('/dashboard/freelancer/profile', name: 'app_freelancer_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FREELANCER');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $form = $this->createForm(FreelancerProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Profile updated.');
            return $this->redirectToRoute('freelancer_dashboard');
        }

        return $this->render('freelancer_dashboard/edit_profile.html.twig', [
            'form' => $form,
        ]);
    }


}
