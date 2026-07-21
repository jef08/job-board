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

        if ($form->isSubmitted() && $form->isValid()) {
            $listing->setUser($this->getUser());
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
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        if ($listing->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not own this listing.');
        }

        return $this->render('listing/applicants.html.twig', [
            'listing' => $listing,
            'applications' => $listing->getApplications(),
        ]); 
    }

    #[Route('/listing/{id}/edit', name: 'app_listing_edit')]
    public function editListing(Listing $listing, Request $request, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        if ($listing->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

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

    #[Route('/listing/{id}/close', name: 'app_listing_close')]
    public function closeListing(Listing $listing, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        if($listing->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $listing->setStatus('closed');
        $em->flush();

        return $this->redirectToRoute('company_dashboard');
    }

    #[Route('/listing/{id}/delete', name: 'app_listing_delete')]
    public function deleteListing(Listing $listing, EntityManagerInterface $em): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        if ($listing->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($listing);
        $em->flush();

        return $this->redirectToRoute('company_dashboard');
    }
}
