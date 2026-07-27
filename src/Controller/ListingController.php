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
use App\Enum\ListingStatus;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

final class ListingController extends AbstractController
{

    private function generateUniqueSlug(string $title, SluggerInterface $slugger, ListingRepository $listingRepository, ?int $excludeId = null): string
    {
        $baseSlug = (string) $slugger->slug($title)->lower();
        $slug = $baseSlug;
        $suffix = 2;

        while (true) {
            $existing = $listingRepository->findOneBySlug($slug);

            // No collision, or the only collision is this same listing (editing) — safe to use
            if ($existing === null || $existing->getId() === $excludeId) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }

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
    public function new(Request $request, EntityManagerInterface $em, ListingRepository $listingRepository, SluggerInterface $slugger): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');

        $listing = new Listing();

        $form = $this->createForm(ListingFormType::class, $listing);
        $form->handleRequest($request);

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $listing->setCompany($user->getCompany());
            $listing->setSlug($this->generateUniqueSlug($listing->getTitle(), $slugger, $listingRepository));
            $em->persist($listing);
            $em->flush();

            return $this->redirectToRoute('company_dashboard');
        }

        return $this->render('listing/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/listing/{id}', name: 'app_listing_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Listing $listing): Response {
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
    // editListing()
    public function editListing(Listing $listing, Request $request, EntityManagerInterface $em, ListingRepository $listingRepository, SluggerInterface $slugger): Response {
        $this->denyAccessUnlessGranted(ListingVoter::EDIT, $listing);

        $form = $this->createForm(ListingFormType::class, $listing);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listing->setSlug($this->generateUniqueSlug($listing->getTitle(), $slugger, $listingRepository, $listing->getId()));
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

        $listing->setStatus(ListingStatus::Closed);
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
