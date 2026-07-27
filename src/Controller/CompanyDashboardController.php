<?php

namespace App\Controller;

use App\Repository\ListingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\CompanyProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/dashboard/company/profile', name: 'app_company_profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $form = $this->createForm(CompanyProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Profile updated.');
            return $this->redirectToRoute('company_dashboard');
        }

        return $this->render('company_dashboard/edit_profile.html.twig', [
            'form' => $form,
        ]);
    }
}
