<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Listing;
use App\Form\ListingFormType;
use App\Repository\ListingRepository;
use App\Repository\CategoryRepository;
use App\Security\Voter\ListingVoter;

final class ListingController extends AbstractController
{
    #[Route('/listing', name: 'app_listing')]
    public function index(ListingRepository $listingRepository, Request $request, CategoryRepository $categoryRepository): Response
    {

        $search = $request->query->get('search');
        $category = $request->query->getInt('category');

        $listings = $listingRepository->findFiltered($search, $category ?: null);

        $categories = $categoryRepository->findAll();

        return $this->render('listing/index.html.twig', [
            'listings' => $listings,
            'categories' => $categories,
        ]);
    }

    #[Route('/listing/new', name: 'app_listing_new')]
    public function new(Request $request, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        $listing = new Listing();

        $form = $this->createForm(ListingFormType::class, $listing);
        $form->handleRequest($request);

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $listing->setCompany($user->getCompany());
            $em->persist($listing);
            $em->flush();

            return $this->redirectToRoute('company_dashboard');
        }

        return $this->render('listing/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/listing/{id}', name: 'app_listing_show')]
    public function show(Listing $listing): Response {
        return $this->render('listing/show.html.twig', [
            'listing' => $listing,
        ]);
    }

    #[Route('/listing/{id}/applicants', name: 'app_listing_applicants')]
    public function applicants(Listing $listing): Response {

        $this->denyAccessUnlessGranted(ListingVoter::EDIT, $listing);

        return $this->render('listing/applicants.html.twig', [
            'listing' => $listing,
            'applications' => $listing->getApplications(),
        ]); 
    }

    #[Route('/listing/{id}/edit', name: 'app_listing_edit', methods: ['GET', 'POST'])]
    public function editListing(Listing $listing, Request $request, EntityManagerInterface $em): Response {

        $this->denyAccessUnlessGranted(ListingVoter::EDIT, $listing);

        $form = $this->createForm(ListingFormType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('company_dashboard');
        }

        return $this->render('listing/edit.html.twig', [
            'form' => $form,
            'listing' => $listing,
        ]);
    }

    #[Route('/listing/{id}/close', name: 'app_listing_close', methods: ['POST'])]
    public function closeListing(Listing $listing, EntityManagerInterface $em, Request $request): Response {

        $this->denyAccessUnlessGranted(ListingVoter::EDIT, $listing);

        if (!$this->isCsrfTokenValid('close-listing-' . $listing->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $listing->setStatus('closed');
        $em->flush();

        return $this->redirectToRoute('company_dashboard');
    }

    #[Route('/listing/{id}/delete', name: 'app_listing_delete', methods: ['POST'])]
    public function deleteListing(Listing $listing, EntityManagerInterface $em, Request $request): Response {

        $this->denyAccessUnlessGranted(ListingVoter::EDIT, $listing);

        if (!$this->isCsrfTokenValid('delete-listing-' . $listing->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($listing);
        $em->flush();

        return $this->redirectToRoute('company_dashboard');
    }
}
